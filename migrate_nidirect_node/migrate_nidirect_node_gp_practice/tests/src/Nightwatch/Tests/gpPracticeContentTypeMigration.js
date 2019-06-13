module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-gp-practice'],

  'Test whether GP Practice content type exists': function (browser) {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/gp-practice')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit gp practice content type/i);
  },
  'Test whether GP Practice content type field exists (field_foo)': browser => {
    browser
      .drupalRelativeURL('/admin/structure/types/manage/page/fields')
      .expect.element('#field-foo').to.be.present;
  },
  'Test whether Page content type field exists (field_bar)': browser => {
    browser.expect.element('#field-bar').to.be.present;
  },
  'Test whether we have any migrated content for GP Practice nodes': browser => {
    browser
      .drupalRelativeURL('/admin/content?type=gp_practice')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
