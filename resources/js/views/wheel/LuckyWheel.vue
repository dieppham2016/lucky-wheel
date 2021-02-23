<template>
  <el-col :style="{background: '#0a76a4', height: _windowHeight}">
    <div class="lucky-wheel--background--bottom" />

    <top-bar :bonus-enable="bonusEnable" :time-play="timePlay" :current-bonus="bonus" />

    <el-row :style="{ height: _windowHeight}">
      <wheel ref="wheel" :segments="segments" :max-segment="segments.length" @spinStop="handleSpinStop" />
    </el-row>

    <bottom-bar :credit="storeGame.coin" @handleStart="startPin" />

    <win-prize-dialog :is-show-winning-dialog="isShowWinningDialog" :prize-winning="prizeWinning" />

    <demo-dialog :is-show-demo-dialog="isShowDemoDialog" :credit="storeGame.coin" @addCoin="handleAddCoin" />
  </el-col>
</template>

<script>
import Wheel from '@/views/wheel/components/Wheel';
import Resource from '@/api/resource';
import TopBar from '@/views/wheel/components/TopBar';
import BottomBar from '@/views/wheel/components/BottomBar';
import WinPrizeDialog from '@/views/wheel/components/WinPrizeDialog';
import DemoDialog from '@/views/wheel/components/DemoDialog';

const Gpio = require('pigpio').Gpio;
const pin_coin = new Gpio(17, { mode: Gpio.INPUT });

const PatternResource = new Resource('wheel/patterns');
const ConfigResource = new Resource('wheel/config');
const StoreResource = new Resource('wheel/store');

export default {
  name: 'LuckyWheel',
  components: { DemoDialog, WinPrizeDialog, BottomBar, TopBar, Wheel },
  data() {
    return {
      timePlay: 0,
      coin: 0,
      bonus: 0,
      clock: null,
      patternData: [],
      degrees: null,
      config: [],
      storeGame: [],
      segments: [],
      bonusEnable: false,
      wheelLoading: false,
      isWheelReady: false,
      isShowWinningDialog: false,
      isShowDemoDialog: true,
      prizeWinning: null,
      windowHeight: window.innerHeight,
      windowWidth: window.innerWidth,
    };
  },
  computed: {
    _windowWidth() {
      return this.windowWidth + 'px';
    },
    _windowHeight() {
      return this.windowHeight + 'px';
    },
  },
  watch: {},
  created() {
    this.getPatternData();
    this.getConfig();
    this.getStoreGame();
  },
  mounted() {
    this.$nextTick(() => {
      window.addEventListener('resize', this.onResize);
    });
  },
  methods: {
    async getPatternData() {
      this.wheelLoading = true;
      const { data } = await PatternResource.index();
      this.patternData = data;
      this.bonusEnable = !!this.patternData[0].bonus_enable;
      if (this.bonusEnable) {
        this.bonus = this.patternData[0].bonus_auto_increment ? this.patternData[0].bonus_current : this.patternData[0].bonus_fixed;
      }
      this.wheelLoading = false;
      this.segments = this.detachSegments();
    },
    async getConfig() {
      const { data } = await ConfigResource.index();
      this.config = data[0];
      this.timePlay = this.mills2second(this.config.time_auto_play);
    },
    async getStoreGame() {
      const { data } = await StoreResource.index();
      this.storeGame = data[0];
    },
    getBonusData(callback) {
      PatternResource.index({ only: ['bonus_current', 'bonus_enable'] }).then((response) => {
        if (response.data[0].bonus_enable) {
          this.bonus = response.data[0].bonus_current;
        }
        if (callback) {
          callback();
        }
      });
    },
    detachSegments() {
      const canvas = document.getElementById('canvas');
      const context = canvas.getContext('2d');
      const gradientRed = context.createRadialGradient(this.windowHeight / 1.3, this.windowHeight / 1.3, this.windowHeight / 1.3, 0, 0, 0);
      const gradientGold = context.createRadialGradient(this.windowHeight / 1.3, this.windowHeight / 1.3, this.windowHeight / 1.3, 0, 0, 0);

      gradientRed.addColorStop(0, '#cf0834');
      gradientRed.addColorStop(0.5, '#d02a4e');
      gradientRed.addColorStop(1, '#cb1c42');

      gradientGold.addColorStop(0, '#eebf5f');
      gradientGold.addColorStop(0.5, '#eece5e');
      gradientGold.addColorStop(1, '#eaba58');

      const segments = [];
      const prizes = [...this.patternData[0].prizes];
      const prizeLocation = [...this.patternData[0].prizes_location];
      prizeLocation.forEach((location, index) => {
        if (index === 0) {
          segments[0] = {
            text: 'Bonus',
            fillStyle: '#25b66f',
            textFillStyle: '#efefef',
            prize: 'Bonus',
            amount: this.patternData[0].bonus_current,
          };
        } else {
          const prize = prizes.filter(e => e.id === location)[0];
          segments[index] = {
            text: ['Ticket'].includes(prize.type) ? prize.amount.toString() : ['Code'].includes(prize.type) ? 'Code' : prize.name,
            fillStyle: index % 2 !== 0 ? gradientRed : gradientGold,
            textFillStyle: '#efefef',
            prize: prize.name,
            amount: prize.amount,
          };
        }
      });
      return segments;
    },
    handleSpinStop(segment, segmentIndex) {
      this.isShowWinningDialog = true;
      this.prizeWinning = segment.prize.toString();
      this.bonusReset(segment);
      this.hideWinningDialog(segment);
      if (this.storeGame.coin < 1) {
        this.showDemoDialog();
      } else {
        this.isWheelReady = true;
        setTimeout(() => {
          if (this.isWheelReady) {
            this.countDown();
          }
        }, 3000);
      }
    },
    onResize() {
      this.windowHeight = window.innerHeight;
      this.windowWidth = window.innerWidth;
    },
    startPin() {
      if (this.storeGame.coin > 0 && this.isWheelReady) {
        clearInterval(this.clock);
        this.timePlay = 0;
        this.isWheelReady = false;
        StoreResource.update(1, { coin: -1 }).then(() => {
          this.getStoreGame().then(() => {
            this.$refs.wheel.handleStart();
          });
        });
      }
    },
    bonusIncrement(callback) {
      PatternResource.update(1, { bonusIncrement: 1 }).then(() => {
        this.getBonusData(() => {
          callback();
        });
      });
    },
    bonusReset(segment, callback) {
      if (segment.prize.toString() === 'Bonus') {
        PatternResource.update(1, { bonusReset: 1 }).then(() => {
          this.getBonusData(() => {
            if (callback) {
              callback();
            }
          });
        });
      }
    },
    handleAddCoin() {
      StoreResource.update(1, { coin: 1 }).then(() => {
        if (this.patternData[0].bonus_auto_increment) {
          this.bonusIncrement(() => {
            this.getStoreGame().then(() => {
              this.isWheelReady = true;
              setTimeout(() => {
                this.isShowDemoDialog = false;
              }, 200);
              this.countDown();
            });
          });
        } else {
          this.getStoreGame().then(() => {
            this.isWheelReady = true;
            setTimeout(() => {
              this.isShowDemoDialog = false;
            }, 200);
            this.countDown();
          });
        }
      });
    },
    hideWinningDialog(segment) {
      const time = segment.name !== 'Bonus' ? this.config.time_show_congratulation_short : this.config.time_show_congratulation_long;
      setTimeout(() => {
        this.isShowWinningDialog = false;
      }, time);
    },
    showDemoDialog() {
      setTimeout(() => {
        this.isShowDemoDialog = true;
      }, this.config.time_back_demo);
    },
    mills2second(mills) {
      return mills / 1000;
    },
    countDown() {
      this.timePlay = this.mills2second(this.config.time_auto_play);
      this.clock = setInterval(() => {
        this.timePlay -= 1;
        if (this.timePlay <= 0) {
          this.isWheelReady = true;
          this.startPin();
          clearInterval(this.clock);
        }
      }, 1000);
    },
  },
};
</script>

<style scoped lang="scss">

.lucky-wheel--background--bottom {
  width: 100vw;
  height: 100vh;
  display: block;
  position: absolute;
  background: #065887;
  clip-path: polygon(0vw 50vh, 0vw 100vh, 100vw 100vh, 100vw 60vh, 70vw 60vh, 55vw 50vh, 50vw 50vh);
}

</style>
