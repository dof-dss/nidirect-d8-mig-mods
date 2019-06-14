module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-publication'],

  'Test whether Publication content type exists': function (browser) {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/publication')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit publication content type/i);
  },
  'Test whether Publication content type field exists (field_top_level_theme)': browser => {
    browser
      .drupalRelativeURL('/admin/structure/types/manage/publication/fields')
      .expect.element('#field-top-level-theme').to.be.present;
  },
  'Test whether Publication content type field exists (field_subtheme)': browser => {
    browser.expect.element('#field-subtheme').to.be.present;
  },
  'Test whether Publication content type field exists (field_site_topics)': browser => {
    browser.expect.element('#field-site-topics').to.be.present;
  },
  'Test whether Publication content type field exists (field_meta_tags)': browser => {
    browser.expect.element('#field-meta-tags').to.be.present;
  },
  'Test whether Publication content type field exists (field_publication_type)': browser => {
    browser.expect.element('#field-publication-type').to.be.present;
  },
  'Test whether Publication content type field exists (field_published_date)': browser => {
    browser.expect.element('#field-published-date').to.be.present;
  },
  'Test whether Publication content type field exists (field_summary)': browser => {
    browser.expect.element('#field-summary').to.be.present;
  },
  'Test whether Publication content type field exists (body)': browser => {
    browser.expect.element('#body').to.be.present;
  },
  'Test whether Publication content type field exists (field_attachment)': browser => {
    browser.expect.element('#field-attachment').to.be.present;
  },
  'Test whether we have any migrated content for publication nodes': browser => {
    browser
      .drupalRelativeURL('/admin/content?type=publication')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
