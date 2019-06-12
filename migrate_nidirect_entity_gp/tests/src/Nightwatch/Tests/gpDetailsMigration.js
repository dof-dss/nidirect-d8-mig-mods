module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-entity-gp'],

  'Test whether GP entity exists': function (browser) {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/page')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit page content type/i);
  }
};
