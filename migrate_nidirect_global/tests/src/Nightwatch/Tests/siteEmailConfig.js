module.exports = {
  '@tags': ['nidirect-migrations'],

  'Test whether site email address matches a known value': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/config/system/site-information')
      .expect.element('#edit-site-mail')
      .to.have.value.that.equals('aeon@example.com');
  },
};
