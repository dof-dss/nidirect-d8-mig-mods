module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-page'],

  'Test whether Page content type exists': function (browser) {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/page')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit page content type/i);
  },
  'Test whether Page content type field exists (field_enable_toc)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/page/fields')
      .expect.element('#field-enable-toc').to.be.present;
  },
  'Test whether Page content type field exists (field_meta_tags)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/page/fields')
      .expect.element('#field-meta-tags').to.be.present;
  },
  'Test whether Page content type field exists (body)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/page/fields')
      .expect.element('#body').to.be.present;
  },
  'Test whether we have any migrated content for Page nodes': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/content?type=page')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
