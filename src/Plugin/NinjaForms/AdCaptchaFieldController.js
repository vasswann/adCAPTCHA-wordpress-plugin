const AdCaptchaFieldController = Marionette.Object.extend({
    initialize() {
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

      if (!window.adcap.successToken) {
        nfRadio.channel('fields').request('add:error', id, 'required-error', 'Please complete the I am human box.');
        return;
      }
  
      if (window.adcap.successToken) {
        nfRadio.channel('fields').request('remove:error', id, 'required-error');
        const response = window.adcap.successToken;
        model.set('value', response);
        return;
      }
    },
  });
  
  jQuery(document).ready(() => {
    new AdCaptchaFieldController();
  });
