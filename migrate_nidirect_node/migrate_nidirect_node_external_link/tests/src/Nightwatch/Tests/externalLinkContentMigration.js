var parser = require('xml2json');
var http = require('http');
var nid, node;
const regx_strip_taxoheir = /^-*/gm;

module.exports = {
  '@tags': [
    'nidirect',
    'nidirect_content',
    'nidirect_content_migration',
    'nidirect_content_migration_external_link',
  ],

  before: function (browser) {
    http.get(process.env.TEST_D7_URL + '/migrate/extlink', (response) => {
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

  'Test whether External Link content matches original': browser => {

    browser
      .pause(2000, function () {
        browser
        .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
        .drupalRelativeURL('/node/' + nid + '/edit')
        .waitForElementVisible('body', 1000)
        .expect.element('#edit-title-0-value')
        .to.have.value.which.contains(node.title);

      browser
        .expect.element('#edit-field-link-0-uri')
        .to.have.value.which.contains(node.link_url);

      /* If the link text is blank the XML export plugin will still output it
         using with the URL value so if they match, ignore the text field in
         the edit form. */
      if (node.link_url != node.link_title) {
        browser
          .expect.element('#edit-field-link-0-title')
          .to.have.value.which.contains(node.link_title);
      }

      if (Object.keys(node.subtheme).length !== 0) {
        browser
          .elements("xpath", "//select[@id='edit-field-subtheme']/option[@selected='selected']", function (elements) {
            if (elements.value.length > 0) {
              let subtheme = node.subtheme.split('|');

              elements.value.map(function (item) {
                browser.elementIdAttribute(item.ELEMENT, 'innerText', function (result) {
                  if (result.value.length > 0) {
                    let text = result.value.replace(regx_strip_taxoheir, '');

                    if (subtheme.includes(text)) {
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

      if (Object.keys(node.supplementary).length !== 0) {
        browser
          .elements("xpath", "//select[@id='edit-field-site-themes']/option[@selected='selected']", function (elements) {
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

              browser.assert.equal(elements.value.length, supp_themes.length, 'field-subtheme item count match');
            }
          });
      }
    })
  }
};
