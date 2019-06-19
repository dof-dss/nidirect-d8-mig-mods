module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-external-link'],

  'Test whether External links content type exists': function (browser) {
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/structure/types/manage/external_link')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.external-link-title').text.to.match(/edit external link content type/i);
  },
  'Test whether External link content type fields exist': browser => {
    browser.drupalRelativeURL('/admin/structure/types/manage/external_link/fields');
    // browser.expect.element('#field-enable-toc').to.be.present;
    // browser.expect.element('#field-meta-tags').to.be.present;
    // browser.expect.element('#body').to.be.present;
  },
  'Test whether we have any migrated content for External link nodes': browser => {
    browser
      .drupalRelativeURL('/admin/content?type=external_link')
      .expect.element('#views-form-content-external-link-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
