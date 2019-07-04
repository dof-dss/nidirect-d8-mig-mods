module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-news'],

  'Test whether News content type exists': function (browser) {
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/structure/types/manage/news')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit news content type/i);
  },

  'Test whether News content type fields exists': browser => {
    browser.drupalRelativeURL('/admin/structure/types/manage/news/fields');
    browser.expect.element('#field-meta-tags').to.be.present;
    browser.expect.element('#body').to.be.present;
    browser.expect.element('#field-published-date').to.be.present;
    browser.expect.element('#field-teaser').to.be.present;
    browser.expect.element('#field-summary').to.be.present;
    browser.expect.element('#field-photo').to.be.present;
    browser.expect.element('#field-enable-toc').to.be.present;
  },

  'Test whether we have any migrated content for News nodes': browser => {
    browser
      .drupalRelativeURL('/admin/content?type=news')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
