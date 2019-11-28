var parser = require('xml2json');
var http = require('http');
var nid, node;
const regx_strip_entref = /\s\(\d+\)/gm;
const regx_strip_html = /<([^>]+)>/ig;
const regx_spaceless_html = /(^|>)[ \n\t]+/g;

module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-health-condition'],

  before: function (browser) {
    http.get(process.env.TEST_D7_URL + '/migrate/healthcond', (response) => {
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

  'Test whether Health Condition content matches original': browser => {

    browser
      .pause(2000, function () {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
          .drupalRelativeURL('/node/' + nid + '/edit')
          .waitForElementVisible('body', 1000)
          .expect.element('#edit-title-0-value')
          .to.have.value.which.contains(node.title);

        if (Object.keys(node.alternative_title).length !== 0) {
          browser
            .elements('xpath', "//input[@type='text' and starts-with(@id, 'edit-field-alternative-title-') and string-length(@value) > 0]", function (elements) {
              if (elements.value.length > 0) {
                let alt_titles = node.alternative_title.split('|');

                elements.value.map(function (item) {
                  browser.elementIdAttribute(item.ELEMENT, 'value', function (result) {
                    if (result.value.length > 0) {
                      if (alt_titles.includes(result.value)) {
                        browser.assert.equal(result.value, result.value);
                      } else {
                        browser.assert.fail('field-alternative-title: data missmatch on : ' + result.value);
                      }
                    }
                  });
                });

                browser.assert.equal(elements.value.length, alt_titles.length, 'field-alternative-title item count missmatch');
              }
            });
        }

        if (Object.keys(node.summary).length !== 0) {
          browser
            .expect.element('textarea[data-drupal-selector="edit-field-summary-0-value"]')
            .to.have.value.which.contains(node.summary.replace(regx_spaceless_html, ">"));
        }

        if (Object.keys(node.body).length !== 0) {
          browser
            .expect.element('textarea[data-drupal-selector="edit-body-0-value"]')
            .to.have.value.which.contains(node.body.replace(regx_spaceless_html, ">"));
        }

        if (Object.keys(node.info_source).length !== 0) {
          browser
            .useXpath()
            .expect.element('//*[@id="edit-field-hc-info-source"]/*/input[@checked="checked"]/following-sibling::label')
            .to.have.text.which.contains(node.info_source);
        }

        if (Object.keys(node.related).length !== 0) {
          browser
            .elements("xpath", "//table[@id='field-related-info-values']/tbody/tr/td/div/label[text()='Link text']/parent::div/input[string-length(@value) > 0]", function (elements) {
              if (elements.value.length > 0) {
                let related = node.related.split('|');

                elements.value.map(function (item) {
                  browser.elementIdAttribute(item.ELEMENT, 'value', function (result) {
                    if (result.value.length > 0) {
                      if (related.includes(result.value)) {
                        browser.assert.equal(result.value, result.value);
                      } else {
                        browser.assert.fail('field-related-info: data mismatch on : ' + result.value);
                      }
                    }
                  })
                });

                browser.assert.equal(elements.value.length, related.length, 'field-related-info item count match');
              }
            });
        }

        if (Object.keys(node.published_date).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-published-date-0-value-date')
            .to.have.value.which.contains(node.published_date.replace(regx_strip_html, ''));
        }

        if (Object.keys(node.last_review).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-last-review-date-0-value-date')
            .to.have.value.which.contains(node.last_review.replace(regx_strip_html, ''));
        }

        if (Object.keys(node.next_review).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-next-review-date-0-value-date')
            .to.have.value.which.contains(node.next_review.replace(regx_strip_html, ''));
        }

        if (Object.keys(node.body_location).length !== 0) {
          browser
            .elements("xpath", "//input[@type='text' and starts-with(@id, 'edit-field-hc-body-location-') and string-length(@value) > 0]", function (elements) {
              if (elements.value.length > 0) {
                let body_location = node.body_location.split('|');

                elements.value.map(function (item) {
                  browser.elementIdAttribute(item.ELEMENT, 'value', function (result) {
                    if (result.value.length > 0) {
                      if (body_location.includes(result.value.replace(regx_strip_entref, ''))) {
                        browser.assert.equal(result.value, result.value);
                      } else {
                        browser.assert.fail('field-hc-body-location: data mismatch on : ' + result.value);
                      }
                    }
                  })
                });

                browser.assert.equal(elements.value.length, body_location.length, 'field-hc-body-location item count match');
              }
            });
        }

        if (Object.keys(node.body_system).length !== 0) {
          browser
            .elements("xpath", "//input[@type='text' and starts-with(@id, 'edit-field-hc-body-system-') and string-length(@value) > 0]", function (elements) {
              if (elements.value.length > 0) {
                let body_system = node.body_system.split('|');

                elements.value.map(function (item) {
                  browser.elementIdAttribute(item.ELEMENT, 'value', function (result) {
                    if (result.value.length > 0) {
                      if (body_system.includes(result.value = result.value.replace(regx_strip_entref, ''))) {
                        browser.assert.equal(result.value, result.value);
                      } else {
                        browser.assert.fail('field-hc-body-system: data mismatch on : ' + result.value);
                      }
                    }
                  })
                });

                browser.assert.equal(elements.value.length, body_system.length, 'field-hc-body-system item count match');
              }
            });
        }

        if (Object.keys(node.condition_type).length !== 0) {
          browser
            .elements("xpath", "//input[@type='text' and starts-with(@id, 'edit-field-hc-condition-type-') and string-length(@value) > 0]", function (elements) {
              if (elements.value.length > 0) {
                let condition_type = node.condition_type.split('|');

                elements.value.map(function (item) {
                  browser.elementIdAttribute(item.ELEMENT, 'value', function (result) {
                    if (result.value.length > 0) {
                      if (condition_type.includes(result.value = result.value.replace(regx_strip_entref, ''))) {
                        browser.assert.equal(result.value, result.value);
                      } else {
                        browser.assert.fail('field-hc-condition-type: data mismatch on : ' + result.value);
                      }
                    }
                  })
                });

                browser.assert.equal(elements.value.length, condition_type.length, 'field-hc-condition-type item count match');
              }
            });
        }

        if (Object.keys(node.primary_symptom_1).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-hc-primary-symptom-1-0-target-id')
            .to.have.value.which.contains(node.primary_symptom_1);
        }

        if (Object.keys(node.primary_symptom_2).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-hc-primary-symptom-2-0-target-id')
            .to.have.value.which.contains(node.primary_symptom_2);
        }

        if (Object.keys(node.primary_symptom_3).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-hc-primary-symptom-3-0-target-id')
            .to.have.value.which.contains(node.primary_symptom_3);
        }

        if (Object.keys(node.primary_symptom_4).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-hc-primary-symptom-4-0-target-id')
            .to.have.value.which.contains(node.primary_symptom_4);
        }

        if (Object.keys(node.secondary_symptoms).length !== 0) {
          browser
            .elements("xpath", "//input[@type='text' and starts-with(@id, 'edit-field-hc-secondary-symptoms-') and string-length(@value) > 0]", function (elements) {
              if (elements.value.length > 0) {
                let secondary_symptoms = node.secondary_symptoms.split('|');

                elements.value.map(function (item) {
                  browser.elementIdAttribute(item.ELEMENT, 'value', function (result) {
                    if (result.value.length > 0) {
                      if (secondary_symptoms.includes(result.value = result.value.replace(regx_strip_entref, ''))) {
                        browser.assert.equal(result.value, result.value);
                      } else {
                        browser.assert.fail('field-hc-secondary-symptoms: data mismatch on : ' + result.value);
                      }
                    }
                  })
                });

                browser.assert.equal(elements.value.length, secondary_symptoms.length, 'field-hc-secondary-symptoms item count match');
              }
            });
        }
      });
  }
};
