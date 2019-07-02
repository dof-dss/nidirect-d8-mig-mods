var parser = require('xml2json');
var http = require('http');
var nid, node;
const regx_strip_taxoheir = /^-*/gm;
const regx_strip_html = /<([^>]+)>/ig;

module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-publication'],

  before: function (browser) {
    http.get(process.env.TEST_D7_URL + '/migrate/publication', (response) => {
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

  'Test whether Publication content type exists': function (browser) {
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/structure/types/manage/publication')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit publication content type/i);
  },

  'Test whether Publication content type fields exist': browser => {
    browser.drupalRelativeURL('/admin/structure/types/manage/publication/fields');
    browser.expect.element('#field-top-level-theme').to.be.present;
    browser.expect.element('#field-subtheme').to.be.present;
    browser.expect.element('#field-site-themes').to.be.present;
    browser.expect.element('#field-meta-tags').to.be.present;
    browser.expect.element('#field-publication-type').to.be.present;
    browser.expect.element('#field-published-date').to.be.present;
    browser.expect.element('#field-summary').to.be.present;
    browser.expect.element('#body').to.be.present;
    browser.expect.element('#field-attachment').to.be.present;
  },

  'Test whether we have any migrated content for publication nodes': browser => {
    browser
      .drupalRelativeURL('/admin/content?type=publication')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  },

  'Test whether Publication content matches original': browser => {

    browser
      .drupalRelativeURL('/node/' + nid + '/edit')
      .waitForElementVisible('body', 1000)
      .expect.element('#edit-title-0-value')
      .to.have.value.which.contains(node.title);

    if (Object.keys(node.supplementary).length !== 0) {
      browser
        .elements("xpath", "//select[@id='edit-field-subtheme']/option[@selected='selected']", function (elements) {
          if (elements.value.length > 0) {
            let supp_themes = node.supplementary.split('|');

            elements.value.map(function (item) {
              browser.elementIdAttribute(item.ELEMENT, 'innerText', function (result) {
                if (result.value.length > 0) {
                  let text = result.value.replace(regx_strip_taxoheir, '');

                  if (supp_themes.includes(text)) {
                    browser.assert.equal(text, text);
                  } else {
                    browser.assert.fail('field-subtheme: data mismatch on : ' + text);
                  }
                }
              })
            });

            browser.assert.equal(elements.value.length, supp_themes.length, 'field-site-themes item count match');
          }
        });
    }

    if (Object.keys(node.subtheme).length !== 0) {
      browser
        .elements("xpath", "//select[@id='edit-field-site-themes']/option[@selected='selected']", function (elements) {
          if (elements.value.length > 0) {
            let subtheme = node.subtheme.split('|');

            elements.value.map(function (item) {
              browser.elementIdAttribute(item.ELEMENT, 'innerText', function (result) {
                if (result.value.length > 0) {
                  let text = result.value.replace(regx_strip_taxoheir, '');

                  if (supp_themes.includes(text)) {
                    browser.assert.equal(text, text);
                  } else {
                    browser.assert.fail('field-site-themes: data mismatch on : ' + text);
                  }
                }
              })
            });

            browser.assert.equal(elements.value.length, subtheme.length, 'field-site-themes item count match');
          }
        });
    }

    browser
      .useXpath()
      .expect.element('//*[@id="edit-field-publication-type"]/option[@selected="selected"]')
      .to.have.value.which.contains(node.type);

    browser
      .useCss()
      .expect.element('#edit-field-published-date-0-value-date')
      .to.have.value.which.contains(node.published_date.replace(regx_strip_html, ''));

    if (Object.keys(node.summary).length !== 0) {
      browser
        .expect.element('#edit-field-summary-0-value')
        .to.have.value.which.contains(node.summary);
    }

    if (Object.keys(node.body).length !== 0) {
      browser
        .expect.element('#edit-body-0-value')
        .to.have.value.which.contains(node.body);
    }




  }
};
