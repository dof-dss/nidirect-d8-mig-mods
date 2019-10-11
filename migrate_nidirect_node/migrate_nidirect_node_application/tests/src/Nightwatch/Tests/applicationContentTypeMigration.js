module.exports = {
  '@tags': ['nidirect-migrations-config'],

  'Test whether Application content type exists': browser => {
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/structure/types/manage/application')
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
  }

};
