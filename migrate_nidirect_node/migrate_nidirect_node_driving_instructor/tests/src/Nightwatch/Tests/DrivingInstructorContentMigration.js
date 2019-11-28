var parser = require('xml2json');
var http = require('http');
var nid, node;
const regx_strip_taxoheir = /^-*/gm;

module.exports = {
  '@tags': ['nidirect-migrations'],

  before: function (browser) {

    http.get(process.env.TEST_D7_URL + '/migrate/drivinginstr', (response) => {
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

  'Test whether Driving Instructor content matches original': browser => {

    browser
      .pause(9000, function () {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
          .drupalRelativeURL('/node/' + nid + '/edit')
          .waitForElementVisible('body', 1000)
          .expect.element('#edit-title-0-value')
          .to.have.value.which.contains(node.title);

        browser
          .useCss()
          .expect.element('#edit-field-di-firstname-0-value')
          .to.have.value.which.contains(node.first_name);

        browser
          .expect.element('#edit-field-di-firstname-0-value')
          .to.have.value.which.contains(node.first_name);

        browser
          .expect.element('#edit-field-di-lastname-0-value')
          .to.have.value.which.contains(node.last_name);

        if (Object.keys(node.mobile).length !== 0) {
          browser
            .expect.element('#edit-field-telephone-1-telephone-container-telephone-number')
            .to.have.value.which.contains(node.mobile);
        }

        if (Object.keys(node.phone).length !== 0) {
          browser
            .expect.element('#edit-field-telephone-0-telephone-container-telephone-number')
            .to.have.value.which.contains(node.phone);
        }

        if (Object.keys(node.email).length !== 0) {
          browser
            .expect.element('#edit-field-email-address-0-value')
            .to.have.value.which.contains(node.email);
        }

        if (Object.keys(node.website).length !== 0) {
          browser
            .expect.element('#edit-field-link-url-0-uri')
            .to.have.value.which.contains(node.website);
        }

        browser
          .expect.element('#edit-field-di-adi-no-0-value')
          .to.have.value.which.contains(node.adi);

        browser
          .elements('xpath', "//input[starts-with(@id, 'edit-field-di-areas-')][@checked='checked']/following-sibling::label", function (elements) {
            if (elements.value.length > 0) {
              let areas = node.areas.split('|');

              elements.value.map(function (item) {
                browser.elementIdText(item.ELEMENT, function (result) {
                  if (result.value.length > 0) {
                    // Check the D8 form value exists in the D7 data.
                    if (areas.includes(result.value)) {
                      // It stinks but it's a simple way to show this assertion passes, else fail below.
                      browser.assert.equal(result.value, result.value);
                    } else {
                      browser.assert.fail('field-di-areas: data mismatch on : ' + result.value);
                    }
                  }
                });
              });
            }
          });

        browser
          .elements('xpath', "//div[@id='edit_field_di_categories_chosen']/*/li[@class='search-choice']/span", function (elements) {
            if (elements.value.length > 0) {
              let categories = node.categories.split(',');

              elements.value.map(function (item) {
                browser.elementIdText(item.ELEMENT, function (result) {
                  if (result.value.length > 0) {
                    let area = result.value.replace(regx_strip_taxoheir, '');
                    // Check the D8 form value exists in the D7 data.
                    if (categories.includes(area)) {
                      // It stinks but it's a simple way to show this assertion passes, else fail below.
                      browser.assert.equal(area, area);
                    } else {
                      browser.assert.fail('field_di_categories: data mismatch on : ' + result.value);
                    }
                  }
                });
              });
            }
          });
      });
  }
};
