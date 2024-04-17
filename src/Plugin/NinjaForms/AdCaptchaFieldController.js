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
      console.log('Updating field...');
  
      const id = model.get('id');
      console.log(id);

      if (!window.adcap.successToken) return;
  
      if (window.adcap.successToken) {
        console.log('Value is set, removing error...');
        nfRadio.channel('fields').request('remove:error', id, 'required-error');
        return;
      }
    },
  });
  
  jQuery(document).ready(() => {
    console.log('Document is ready, creating new AdCaptchaFieldController...');
    new AdCaptchaFieldController();
  });
