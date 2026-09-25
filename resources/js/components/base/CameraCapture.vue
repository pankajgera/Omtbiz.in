<template>
  <div class="camera-capture" role="dialog" aria-modal="true" :aria-label="$t('general.take_photo')">
    <div class="camera-capture__panel">
      <div class="camera-capture__header">
        <h5 class="camera-capture__title">{{ $t('general.take_photo') }}</h5>
        <button
          type="button"
          class="camera-capture__close"
          :aria-label="$t('general.cancel')"
          @click="close"
        >
          <font-awesome-icon :icon="['fas', 'times']" />
        </button>
      </div>

      <div class="camera-capture__stage">
        <!-- playsinline is what stops iOS Safari from yanking the preview into
             its own fullscreen player the moment it starts. muted is required
             for autoplay to be allowed at all. -->
        <video
          v-show="!error && !photo"
          ref="video"
          class="camera-capture__video"
          autoplay
          playsinline
          muted
        />
        <img v-if="photo" :src="photo" class="camera-capture__preview" alt="">

        <p v-if="error" class="camera-capture__error">{{ error }}</p>
        <p v-else-if="isStarting" class="camera-capture__status">{{ $t('general.starting_camera') }}</p>
      </div>

      <div class="camera-capture__actions">
        <template v-if="photo">
          <base-button color="theme" :icon="['fas', 'check']" @click="usePhoto">
            {{ $t('general.use_photo') }}
          </base-button>
          <base-button :outline="true" color="theme" :icon="['fas', 'sync']" @click="retake">
            {{ $t('general.retake') }}
          </base-button>
        </template>
        <template v-else>
          <base-button
            color="theme"
            :icon="['fas', 'camera']"
            :disabled="!isReady"
            @click="capture"
          >
            {{ $t('general.capture') }}
          </base-button>
          <base-button
            v-if="canSwitch"
            :outline="true"
            color="theme"
            :icon="['fas', 'sync-alt']"
            :disabled="!isReady"
            @click="switchCamera"
          >
            {{ $t('general.switch_camera') }}
          </base-button>
        </template>
        <base-button :outline="true" color="theme" @click="close">
          {{ $t('general.cancel') }}
        </base-button>
      </div>
    </div>
  </div>
</template>
<script>
// Live camera capture. The frame is pulled straight off the MediaStream into a
// canvas and handed back as a JPEG data URL - it never becomes a file on the
// device, so nothing lands in the phone's gallery or Files. That is the whole
// reason this exists rather than `<input type="file" capture>`, which hands the
// job to the OS camera app and on most Android builds saves a copy to DCIM.
//
// Emits `capture` with the data URL (same shape the file-upload path produces,
// so callers can treat both identically) and `close` when dismissed.
export default {
  props: {
    // Longest edge of the saved image. Full-resolution phone cameras produce
    // data URLs in the multi-MB range once base64-encoded, which is a lot to
    // post for a bill photo.
    maxDimension: {
      type: Number,
      default: 1600
    },
    quality: {
      type: Number,
      default: 0.85
    }
  },
  emits: ['capture', 'close'],
  data () {
    return {
      stream: null,
      photo: '',
      error: '',
      isStarting: true,
      isReady: false,
      canSwitch: false,
      facingMode: 'environment',
    }
  },
  async mounted () {
    this.canSwitch = await this.hasMultipleCameras()
    this.start()
  },
  // Both hooks on purpose: `unmounted` is the Vue 3 name, but this app runs the
  // migration build where a stray `destroyed` would be silently ignored. Losing
  // this means the camera stays live (and its indicator light on) after close.
  unmounted () {
    this.stopStream()
  },
  methods: {
    async hasMultipleCameras () {
      if (! navigator.mediaDevices || ! navigator.mediaDevices.enumerateDevices) {
        return false
      }
      try {
        let devices = await navigator.mediaDevices.enumerateDevices()
        return devices.filter(d => d.kind === 'videoinput').length > 1
      } catch (err) {
        return false
      }
    },
    async start () {
      this.isStarting = true
      this.isReady = false
      this.error = ''

      // getUserMedia only exists in a secure context. Over plain http on a
      // phone (i.e. anything but localhost) the property is simply absent, so
      // say why rather than failing with a bare undefined error.
      if (! navigator.mediaDevices || ! navigator.mediaDevices.getUserMedia) {
        this.error = this.$t('general.camera_unavailable')
        this.isStarting = false
        return
      }

      try {
        this.stream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: { ideal: this.facingMode },
            width: { ideal: 1920 },
            height: { ideal: 1080 },
          },
          audio: false,
        })
        this.$refs.video.srcObject = this.stream
        await this.$refs.video.play()
        this.isReady = true
      } catch (err) {
        this.error = err && err.name === 'NotAllowedError'
          ? this.$t('general.camera_denied')
          : this.$t('general.camera_unavailable')
      } finally {
        this.isStarting = false
      }
    },
    stopStream () {
      if (this.stream) {
        this.stream.getTracks().forEach(track => track.stop())
        this.stream = null
      }
      if (this.$refs.video) {
        this.$refs.video.srcObject = null
      }
      this.isReady = false
    },
    async switchCamera () {
      this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment'
      this.stopStream()
      await this.start()
    },
    capture () {
      let video = this.$refs.video
      if (! video || ! video.videoWidth) {
        return
      }

      let scale = Math.min(1, this.maxDimension / Math.max(video.videoWidth, video.videoHeight))
      let canvas = document.createElement('canvas')
      canvas.width = Math.round(video.videoWidth * scale)
      canvas.height = Math.round(video.videoHeight * scale)
      canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height)

      this.photo = canvas.toDataURL('image/jpeg', this.quality)
      // Freeze the camera while the shot is being reviewed - no reason to keep
      // it running (and lit) behind a still.
      this.stopStream()
    },
    async retake () {
      this.photo = ''
      await this.start()
    },
    usePhoto () {
      this.$emit('capture', this.photo)
      this.close()
    },
    close () {
      this.stopStream()
      this.$emit('close')
    },
  }
}
</script>
