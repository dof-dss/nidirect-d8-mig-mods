var parser = require('xml2json');
var http = require('http');
var nid, node;

module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-gp-practice'],

  before: function (browser) {
    http.get(process.env.TEST_D7_URL + '/migrate/gppractice', (response) => {
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

  'Test whether GP Practice content matches original': browser => {

    browser
      .pause(9000, function () {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
          .drupalRelativeURL('/node/' + nid + '/edit')
          .waitForElementVisible('body', 1000)
          .expect.element('#edit-title-0-value')
          .to.have.value.which.contains(node.title);

        if (Object.keys(node.practice_name).length !== 0) {
          browser
            .expect.element('#edit-field-gp-practice-name-0-value')
            .to.have.value.which.contains(node.practice_name);
        }

        browser
          .expect.element('#edit-field-gp-practice-no-0-value')
          .to.have.value.which.contains(node.practice_number);

        if (Object.keys(node.surgery_name).length !== 0) {
          browser
            .expect.element('#edit-field-gp-surgery-name-0-value')
            .to.have.value.which.contains(node.surgery_name);
        }

        browser
          .expect.element('#edit-field-gp-partnership-no-0-value')
          .to.have.value.which.contains(node.partnership_number);

        browser
          .expect.element('#edit-field-gp-lcg')
          .to.have.value.which.contains(node.lcg);

        if (Object.keys(node.appointments_url).length !== 0) {
          browser
            .expect.element('#edit-field-gp-appointments-0-uri')
            .to.have.value.which.contains(node.appointments_url);
        }

        if (Object.keys(node.appointments_title).length !== 0) {
          browser
            .expect.element('#edit-field-gp-appointments-0-title')
            .to.have.value.which.contains(node.appointments_title);
        }

        if (Object.keys(node.prescriptions_url).length !== 0) {
          browser
            .expect.element('#edit-field-gp-prescriptions-0-uri')
            .to.have.value.which.contains(node.prescriptions_url);
        }

        if (Object.keys(node.prescriptions_title).length !== 0) {
          browser
            .expect.element('#edit-field-gp-prescriptions-0-title')
            .to.have.value.which.contains(node.prescriptions_title);
        }

        if (Object.keys(node.practice_url).length !== 0) {
          browser
            .expect.element('#edit-field-gp-practice-website-0-uri')
            .to.have.value.which.contains(node.practice_url);
        }

        if (Object.keys(node.practice_title).length !== 0) {
          browser
            .expect.element('#edit-field-gp-practice-website-0-title')
            .to.have.value.which.contains(node.practice_title);
        }

        if (Object.keys(node.phone).length !== 0) {
          browser
            .expect.element('#edit-field-telephone-0-telephone-container-telephone-number')
            .to.have.value.which.contains(node.phone);
        }

        if (Object.keys(node.address_1).length !== 0) {
          browser
            .expect.element('#edit-field-address-0-address-address-line1')
            .to.have.value.which.contains(node.address_1);
        }

        // NOTE: Look like address has been combined in the D8 site.
        // if (Object.keys(node.address_2).length !== 0) {
        //   browser
        //     .expect.element('#edit-field-address-0-address-address-line2')
        //     .to.have.value.which.contains(node.address_2);
        // }

        if (Object.keys(node.town).length !== 0) {
          browser
            .expect.element('#edit-field-address-0-address-locality')
            .to.have.value.which.contains(node.town);
        }

        if (Object.keys(node.postcode).length !== 0) {
          browser
            .expect.element('#edit-field-address-0-address-postal-code')
            .to.have.value.which.contains(node.postcode);
        }

        browser
          .expect.element('#edit-field-gp-practice-lead-0-target-id')
          .to.have.value.which.contains(node.lead_gp);

        if (Object.keys(node.practice_gps).length !== 0) {
          browser
            .elements("xpath", "//*[@id='field-gp-practice-member-values']/tbody/tr/td/div/input[@type='text' and string-length(@value) > 0]", function (elements) {
              if (elements.value.length > 0) {
                let practice_gps = node.practice_gps.split('|');

                // TODO: Leaving this here but cannot test as the entity display doesn't match that of the D7 site.
                // elements.value.map(function (item) {
                //   browser.elementIdAttribute(item.ELEMENT, 'value', function (result) {
                //     if (result.value.length > 0) {
                //       if (practice_gps.includes(result.value)) {
                //         browser.assert.equal(result.value, result.value);
                //       } else {
                //         browser.assert.fail('field-gp-practice-member: data mismatch on : ' + result.value);
                //       }
                //     }
                //   })
                // });

                browser.assert.equal(elements.value.length, practice_gps.length, 'field-gp-practice-member item count match');
              }
            });
        }
      });
  }
};
