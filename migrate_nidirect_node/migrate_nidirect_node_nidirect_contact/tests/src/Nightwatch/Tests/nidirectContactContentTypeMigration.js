module.exports = {
  '@tags': ['nidirect-migrations-config'],

  'Test whether Contact content type exists': browser => {
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/structure/types/manage/contact')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit contact content type/i);
  },

  'Test whether Contact content type fields exist': browser => {
    browser.drupalRelativeURL('/admin/structure/types/manage/contact/fields');
    browser.expect.element('#field-contact-additional-info').to.be.present;
    browser.expect.element('#field-address').to.be.present;
    browser.expect.element('#body').to.be.present;
    browser.expect.element('#field-contact-category').to.be.present;
    browser.expect.element('#field-email-address').to.be.present;
    browser.expect.element('#field-contact-fax	').to.be.present;
    browser.expect.element('#field-location').to.be.present;
    browser.expect.element('#field-meta-tags').to.be.present;
    browser.expect.element('#field-contact-hours').to.be.present;
    browser.expect.element('#field-contact-group').to.be.present;
    browser.expect.element('#field-contact-phone').to.be.present;
    browser.expect.element('#field-site-themes').to.be.present;
    browser.expect.element('#field-summary').to.be.present;
    browser.expect.element('#field-supplementary-contact').to.be.present;
    browser.expect.element('#field-contact-mobile').to.be.present;
    browser.expect.element('#field-contact-website').to.be.present;
  }

};
