module.exports = {
  '@tags': [
    'nidirect',
    'nidirect_config',
    'nidirect_config_nidirect_contact',
  ],

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
    browser.expect.element('#field-location').to.be.present;
    browser.expect.element('#field-meta-tags').to.be.present;
    browser.expect.element('#field-contact-hours').to.be.present;
    browser.expect.element('#field-contact-group').to.be.present;
    browser.expect.element('#field-telephone').to.be.present;
    browser.expect.element('#field-site-themes').to.be.present;
    browser.expect.element('#field-summary').to.be.present;
    browser.expect.element('#field-supplementary-contact').to.be.present;
    browser.expect.element('#field-contact-website').to.be.present;
  },

  'Test whether we have any migrated content for Contact nodes': browser => {
    browser
      .drupalRelativeURL('/admin/content?type=contact')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
