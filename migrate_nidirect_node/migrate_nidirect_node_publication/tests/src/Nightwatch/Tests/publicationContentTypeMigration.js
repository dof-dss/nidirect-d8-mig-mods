module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-publication'],

  'Test whether Publication content type exists': function (browser) {
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/structure/types/manage/publication')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit publication content type/i);
  },
  'Test whether Publication content type fields exist': browser => {
    browser.drupalRelativeURL('/admin/structure/types/manage/publication/fields');
    browser.expect.element('#field-top-level-theme').to.be.present;
    browser.expect.element('#field-subtheme').to.be.present;
    browser.expect.element('#field-site-themes').to.be.present;
    browser.expect.element('#field-meta-tags').to.be.present;
    browser.expect.element('#field-publication-type').to.be.present;
    browser.expect.element('#field-published-date').to.be.present;
    browser.expect.element('#field-summary').to.be.present;
    browser.expect.element('#body').to.be.present;
    browser.expect.element('#field-attachment').to.be.present;
  },
  'Test whether we have any migrated content for publication nodes': browser => {
    browser
      .drupalRelativeURL('/admin/content?type=publication')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
