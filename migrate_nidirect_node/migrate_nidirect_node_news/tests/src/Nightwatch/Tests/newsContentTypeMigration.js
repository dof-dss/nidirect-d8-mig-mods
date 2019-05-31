module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-news'],

  'Test whether News content type exists': function (browser) {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/news')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.news-title').text.to.match(/edit news content type/i);
  },
  'Test whether News content type field exists (field_meta_tags)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/news/fields')
      .expect.element('#field-meta-tags').to.be.present;
  },
  'Test whether News content type field exists (body)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/news/fields')
      .expect.element('#body').to.be.present;
  },
  'Test whether News content type field exists (field_published_date)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/news/fields')
      .expect.element('#field-published-date').to.be.present;
  },
  'Test whether News content type field exists (field_teaser)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/news/fields')
      .expect.element('#field-teaser').to.be.present;
  },
  'Test whether News content type field exists (field_summary)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/news/fields')
      .expect.element('#field-summary').to.be.present;
  },
  'Test whether News content type field exists (field_photo)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/news/fields')
      .expect.element('#field-photo').to.be.present;
  },
  'Test whether News content type field exists (field_enable_toc)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/news/fields')
      .expect.element('#field-enable-toc').to.be.present;
  },
  'Test whether we have any migrated content for News nodes': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/content?type=news')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
