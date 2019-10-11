module.exports = {
  '@tags': ['nidirect-migrations-config'],

  'Test whether Page content type exists': function (browser) {
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/structure/types/manage/page')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit page content type/i);
  },
  'Test whether Page content type fields exist': browser => {
    browser.drupalRelativeURL('/admin/structure/types/manage/page/fields');
    browser.expect.element('#field-enable-toc').to.be.present;
    browser.expect.element('#field-meta-tags').to.be.present;
    browser.expect.element('#body').to.be.present;
  }

};
