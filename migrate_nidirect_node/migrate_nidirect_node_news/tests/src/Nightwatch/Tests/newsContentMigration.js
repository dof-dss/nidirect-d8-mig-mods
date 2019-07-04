var parser = require('xml2json');
var http = require('http');
var node, nid;
const regx_strip_html = /<([^>]+)>/ig;

module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-news'],

  before: function (browser) {
    http.get(process.env.TEST_D7_URL + '/migrate/news', (response) => {
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

  'Test whether News content matches original': browser => {

    browser
      .pause(2000, function () {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
          .drupalRelativeURL('/node/' + nid + '/edit')
          .waitForElementVisible('body', 1000)
          .expect.element('#edit-title-0-value')
          .to.have.value.which.contains(node.title);

        browser
          .useCss()
          .expect.element('#edit-field-published-date-0-value-date')
          .to.have.value.which.contains(node.published_date.replace(regx_strip_html, ''));

        browser
          .expect.element('#edit-field-teaser-0-value')
          .to.have.value.which.contains(node.teaser);

        if (Object.keys(node.summary).length !== 0) {
          browser
            .expect.element('#edit-field-summary-0-value')
            .to.have.value.which.contains(node.summary);
        }

        browser
          .expect.element('#edit-field-body-0-value')
          .to.have.value.which.contains(node.body);

        if (node.enable_toc == 1) {
          browser.expect.element('#edit-field-enable-toc-value').to.be.selected;
        } else {
          browser.expect.element('#edit-field-enable-toc-value').to.not.be.selected;
        }
      });

  }

};
