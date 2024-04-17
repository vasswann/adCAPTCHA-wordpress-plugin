const AdCaptchaFieldController = Marionette.Object.extend({
    initialize() {
      console.log('Initializing AdCaptchaFieldController...');
      this.listenTo(
        nfRadio.channel('submit'),
        'validate:field',
        this.updateField
      );
  
      this.listenTo(
        nfRadio.channel('fields'),
        'change:modelValue',
        this.updateField
      );
    },
    updateField(model) {
      if ('adcaptcha' !== model.get('type')) {
        return;
      }
  
      const id = model.get('id');

      if (!window.adcap.successToken) return;
  
      if (window.adcap.successToken) {
        nfRadio.channel('fields').request('remove:error', id, 'required-error');
        return;
      }
    },
  });
  
  jQuery(document).ready(() => {
    new AdCaptchaFieldController();
  });
