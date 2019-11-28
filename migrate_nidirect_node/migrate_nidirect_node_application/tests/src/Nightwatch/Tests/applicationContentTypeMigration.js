module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-application'],

  'Test whether Application content type exists': browser => {
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/structure/types/manage/application')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit application content type/i);
  },

  'Test whether Application content type fields exist': browser => {
    browser.drupalRelativeURL('/admin/structure/types/manage/application/fields');
    browser.expect.element('#field-additional-info').to.be.present;
    browser.expect.element('#body').to.be.present;
    browser.expect.element('#field-link').to.be.present;
    browser.expect.element('#field-meta-tags').to.be.present;
    browser.expect.element('#field-summary').to.be.present;
    browser.expect.element('#field-site-themes').to.be.present;
    browser.expect.element('#field-teaser').to.be.present;
    browser.expect.element('#field-subtheme').to.be.present;
    browser.expect.element('#field-top-level-theme').to.be.present;
  },

  'Test whether we have any migrated content for Application nodes': browser => {
    browser
      .drupalRelativeURL('/admin/content?type=application')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
