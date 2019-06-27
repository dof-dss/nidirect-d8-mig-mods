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
  },

  'Test whether News content matches original': browser => {

    browser.drupalRelativeURL('/node/' + nid + '/edit')
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
  }

};
