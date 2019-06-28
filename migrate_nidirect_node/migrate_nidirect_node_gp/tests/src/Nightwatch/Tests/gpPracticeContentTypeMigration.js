module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-gp-practice'],

  'Test whether GP Practice content type exists': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/gp_practice')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit gp practice content type/i);
  },
  'Test whether GP Practice content type fields exist': browser => {
    browser.drupalRelativeURL('/admin/structure/types/manage/gp_practice/fields')
    browser.expect.element('#field-address').to.be.present
    browser.expect.element('#field-gp-practice-member').to.be.present
    browser.expect.element('#field-gp-practice-lead').to.be.present
    browser.expect.element('#field-gp-lcg').to.be.present
    browser.expect.element('#field-location').to.be.present
    browser.expect.element('#field-meta-tags').to.be.present
    browser.expect.element('#field-gp-appointments').to.be.present
    browser.expect.element('#field-gp-partnership-no').to.be.present
    browser.expect.element('#field-contact-phone').to.be.present
    browser.expect.element('#field-gp-practice-name').to.be.present
    browser.expect.element('#field-gp-practice-no').to.be.present
    browser.expect.element('#field-gp-practice-website').to.be.present
    browser.expect.element('#field-gp-prescriptions').to.be.present
    browser.expect.element('#field-gp-surgery-name').to.be.present;
  },
  'Test for unwanted legacy GP Practice content type fields': browser => {
    browser.expect.element('#field-map').to.not.be.present
    browser.expect.element('#field-contact-address-01').to.not.be.present
    browser.expect.element('#field-contact-address-02').to.not.be.present
    browser.expect.element('#field-contact-town-city').to.not.be.present
    browser.expect.element('#field-contact-postcode').to.not.be.present
    browser.expect.element('#field-map').to.not.be.present;
  },
  'Test whether we have any migrated content for GP Practice nodes': browser => {
    browser
      .drupalRelativeURL('/admin/content?type=gp_practice')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
