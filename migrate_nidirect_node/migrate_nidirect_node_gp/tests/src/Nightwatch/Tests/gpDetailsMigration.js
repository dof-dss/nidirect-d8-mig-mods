var parser = require('xml2json');
var http = require('http');
var nid, node;

module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-entity-gp'],

  before: function (browser) {
    http.get(process.env.TEST_D7_URL + '/migrate/gp', (response) => {
      let data = '';
      response.on('data', (chunk) => { data += chunk });

      response.on('end', () => {
        data = JSON.parse(parser.toJson(data));
        node = data.nodes.node;
        nid = node.nid;
      })
    }).on("error", (err) => {
      console.log("Error: " + err.message);
    });
  },

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
  },

  'Test whether GP content matches original': browser => {

    browser
    .drupalRelativeURL('/gp/' + nid + '/edit')
    .waitForElementVisible('body', 1000)
    .expect.element('#edit-first-name-0-value')
    .to.have.value.which.contains(node.forename);

    browser
    .expect.element('#edit-last-name-0-value')
    .to.have.value.which.contains(node.surname);

    browser
    .expect.element('#edit-cypher-0-value')
    .to.have.value.which.contains(node.cypher);
  }
};
