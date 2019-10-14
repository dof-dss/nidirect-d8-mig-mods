var parser = require('xml2json');
var http = require('http');
var nid, node;

module.exports = {
  '@tags': ['nidirect-migrations-content'],

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

  'Test whether GP content matches original': browser => {

    browser
      .pause(2000, function () {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
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
      });
  }
};
