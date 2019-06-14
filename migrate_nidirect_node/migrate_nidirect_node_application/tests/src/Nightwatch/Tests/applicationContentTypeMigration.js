module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-application'],

  'Test whether Application content type exists': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types')
      .expect.element('#block-seven-content > table > tbody > tr:nth-child(1) > td.menu-label')
      .text.to.equal('Application');
  },
  'Test whether Application content type field exists (field_additional_info)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/application/fields')
      .expect.element('#field-additional-info').to.be.present;
  },
  'Test whether Application content type field exists (body)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/application/fields')
      .expect.element('#body').to.be.present;
  },
  'Test whether Application content type field exists (field_link)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/application/fields')
      .expect.element('#field-link').to.be.present;
  },
  'Test whether Application content type field exists (field_meta_tags)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/application/fields')
      .expect.element('#field-meta-tags').to.be.present;
  },
  'Test whether Application content type field exists (field_summary)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/application/fields')
      .expect.element('#field-summary').to.be.present;
  },
  'Test whether Application content type field exists (field_site_topics)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/application/fields')
      .expect.element('#field-site-topics').to.be.present;
  },
  'Test whether Application content type field exists (field_teaser)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/application/fields')
      .expect.element('#field-teaser').to.be.present;
  },
  'Test whether Application content type field exists (field_subtheme)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/application/fields')
      .expect.element('#field-subtheme').to.be.present;
  },
  'Test whether Application content type field exists (field_top_level_theme)': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/application/fields')
      .expect.element('#field-top-level-theme').to.be.present;
  },
  'Test whether we have any migrated content for Application nodes': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/content?type=application')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
