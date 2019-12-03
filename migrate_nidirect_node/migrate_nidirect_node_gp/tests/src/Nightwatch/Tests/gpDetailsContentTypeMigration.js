module.exports = {
  '@tags': [
    'nidirect',
    'nidirect_config',
    'nidirect_config_gp',
  ],

  'Test whether GP entity exists': function (browser) {
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/structure/gp/settings')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/gp settings/i);
  },

  'Test whether base fields appear on the entity form page': function (browser) {
    browser.drupalRelativeURL('/admin/structure/gp/settings/form-display');
    browser.expect.element('#first-name').to.be.present;
    browser.expect.element('#last-name').to.be.present;
    browser.expect.element('#cypher').to.be.present;
  },

  'Test whether we have any migrated content for GP details': browser => {
    browser
      .drupalRelativeURL('/admin/content/gp')
      .expect.element('table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
