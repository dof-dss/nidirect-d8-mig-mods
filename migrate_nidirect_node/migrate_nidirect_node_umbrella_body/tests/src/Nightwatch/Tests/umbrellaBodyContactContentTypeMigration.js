module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-umbrella-body'],

  'Test whether Umbrella Body content type exists': browser => {
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/structure/types/manage/umbrella_body')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit Umbrella Body content type/i);
  },
  'Test whether Umbrella Body content type fields exist': browser => {
    browser.drupalRelativeURL('/admin/structure/types/manage/umbrella_body/fields');
    browser.expect.element('#field-ub-price-addinfo').to.be.present;
    browser.expect.element('#field-address').to.be.present;
    browser.expect.element('#field-ub-price').to.be.present;
    browser.expect.element('#body').to.be.present;
    browser.expect.element('#field-ub-contact').to.be.present;
    browser.expect.element('#field-ub-counties').to.be.present;
    browser.expect.element('#field-ub-all-ni').to.be.present;
    browser.expect.element('#field-ub-date-issued').to.be.present;
    browser.expect.element('#field-district').to.be.present;
    browser.expect.element('#field-email-address').to.be.present;
    browser.expect.element('#field-livelink-url').to.be.present;
    browser.expect.element('#field-location').to.be.present;
    browser.expect.element('#field-meta-tags').to.be.present;
    browser.expect.element('#field-contact-phone').to.be.present;
    browser.expect.element('#field-ub-sector').to.be.present;
    browser.expect.element('#field-ub-services').to.be.present;
    browser.expect.element('#field-ub-price-volunteer').to.be.present;
    browser.expect.element('#field-contact-website').to.be.present;
  },
  'Test whether we have any migrated content for Umbrella Body nodes': browser => {
    browser
      .drupalRelativeURL('/admin/content?type=umbrella_body')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }

};
