var parser = require('xml2json');
var http = require('http');
var nid, node;
const regx_strip_html = /<([^>]+)>/ig;
const regx_traffic_vals = />(Low|Med|High)<\/.+nutrient-value">([0-9.]+)g<\//gm;

module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-recipe'],

  before: function (browser) {
    http.get(process.env.TEST_D7_URL + '/migrate/recipe', (response) => {
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

  'Test whether Recipe content matches original': browser => {
    browser
      .pause(2000, function () {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
          .drupalRelativeURL('/node/' + nid + '/edit')
          .waitForElementVisible('body', 1000)
          .expect.element('#edit-title-0-value')
          .to.have.value.which.contains(node.title);

        browser
          .expect.element('#edit-field-recipe-description-0-value')
          .to.have.value.which.contains(node.description);

        browser
          .elements("xpath", "//select[@id='edit-field-recipe-main-ingredient']/option[@selected='selected']", function (elements) {
            if (elements.value.length > 0) {
              let main_ingredient = node.main_ingredient.split('|');

              elements.value.map(function (item) {
                browser.elementIdAttribute(item.ELEMENT, 'innerText', function (result) {
                  if (result.value.length > 0) {
                    if (main_ingredient.includes(result.value)) {
                      browser.assert.equal(result.value, result.value);
                    } else {
                      browser.assert.fail('field-recipe-main-ingredient: data mismatch on : ' + result.value);
                    }
                  }
                })
              });

              browser.assert.equal(elements.value.length, main_ingredient.length, 'field-recipe-main-ingredient item count match');
            }
          });

        browser
          .elements("xpath", "//select[@id='edit-field-recipe-course-type']/option[@selected='selected']", function (elements) {
            if (elements.value.length > 0) {
              let course = node.course.split('|');

              elements.value.map(function (item) {
                browser.elementIdAttribute(item.ELEMENT, 'innerText', function (result) {
                  if (result.value.length > 0) {
                    if (course.includes(result.value)) {
                      browser.assert.equal(result.value, result.value);
                    } else {
                      browser.assert.fail('field-recipe-course-type: data mismatch on : ' + result.value);
                    }
                  }
                })
              });

              browser.assert.equal(elements.value.length, course.length, 'field-recipe-course-type item count match');
            }
          });

        if (Object.keys(node.special_diet).length !== 0) {
          browser
            .elements("xpath", "//select[@id='edit-field-recipe-special-diet']/option[@selected='selected']", function (elements) {
              if (elements.value.length > 0) {
                let special_diet = node.special_diet.split('|');

                elements.value.map(function (item) {
                  browser.elementIdAttribute(item.ELEMENT, 'innerText', function (result) {
                    if (result.value.length > 0) {
                      if (special_diet.includes(result.value)) {
                        browser.assert.equal(result.value, result.value);
                      } else {
                        browser.assert.fail('field-recipe-special-diet: data mismatch on : ' + result.value);
                      }
                    }
                  })
                });

                browser.assert.equal(elements.value.length, special_diet.length, 'field-recipe-special-diet item count match');
              }
            });
        }

        browser
          .expect.element('#edit-field-recipe-preptime-0-value')
          .to.have.value.which.contains(node.prep_time.replace(regx_strip_html, ''));

        if (Object.keys(node.season).length !== 0) {
          browser
            .elements("xpath", "//select[@id='edit-field-recipe-season']/option[@selected='selected']", function (elements) {
              if (elements.value.length > 0) {
                let season = node.season.split('|');

                elements.value.map(function (item) {
                  browser.elementIdAttribute(item.ELEMENT, 'innerText', function (result) {
                    if (result.value.length > 0) {
                      if (season.includes(result.value)) {
                        browser.assert.equal(result.value, result.value);
                      } else {
                        browser.assert.fail('field-recipe-season: data mismatch on : ' + result.value);
                      }
                    }
                  })
                });

                browser.assert.equal(elements.value.length, season.length, 'field-recipe-season item count match');
              }
            });
        }

        browser
          .expect.element('#edit-field-recipe-serves')
          .to.have.value.that.equals(node.serves);

        if (node.allergens == 1) {
          browser.expect.element('#edit-field-recipe-allergens-value').to.be.selected;
        } else {
          browser.expect.element('#edit-field-recipe-allergens-value').to.not.be.selected;
        }

        if (Object.keys(node.fat).length !== 0) {
          let fat_vals = regx_traffic_vals.exec(node.fat);

          // Use parseFloat to trim trailing zero from value.
          browser
            .useCss()
            .expect.element('#edit-field-recipe-fat-content-0-value')
            .to.have.value.that.equals(parseFloat(fat_vals[2]));

          browser
            .element("xpath", "//select[@id='edit-field-recipe-fat-content-0-status']/option[@selected='selected']", function (element) {
              browser.elementIdAttribute(element.value.ELEMENT, 'value', function (text) {
                browser.assert.equal(text.value, fat_vals[1].toLowerCase());
              })
            });
          // Reset regex index.
          regx_traffic_vals.lastIndex = 0;
        }

        if (Object.keys(node.sat_fat).length !== 0) {
          let satfat_vals = regx_traffic_vals.exec(node.sat_fat);

          // Use parseFloat to trim trailing zero from value.
          browser
            .useCss()
            .expect.element('#edit-field-recipe-saturates-0-value')
            .to.have.value.that.equals(parseFloat(satfat_vals[2]));

          browser
            .element("xpath", "//select[@id='edit-field-recipe-saturates-0-status']/option[@selected='selected']", function (element) {
              browser.elementIdAttribute(element.value.ELEMENT, 'value', function (text) {
                browser.assert.equal(text.value, satfat_vals[1].toLowerCase());
              })
            });
          // Reset regex index.
          regx_traffic_vals.lastIndex = 0;
        }

        if (Object.keys(node.sugar).length !== 0) {
          let sugar_vals = regx_traffic_vals.exec(node.sugar);

          // Use parseFloat to trim trailing zero from value.
          browser
            .useCss()
            .expect.element('#edit-field-recipe-sugar-0-value')
            .to.have.value.that.equals(parseFloat(sugar_vals[2]));

          browser
            .element("xpath", "//select[@id='edit-field-recipe-sugar-0-status']/option[@selected='selected']", function (element) {
              browser.elementIdAttribute(element.value.ELEMENT, 'value', function (text) {
                browser.assert.equal(text.value, sugar_vals[1].toLowerCase());
              })
            });
          // Reset regex index.
          regx_traffic_vals.lastIndex = 0;
        }

        if (Object.keys(node.salt).length !== 0) {
          let salt_vals = regx_traffic_vals.exec(node.salt);

          // Use parseFloat to trim trailing zero from value.
          browser
            .useCss()
            .expect.element('#edit-field-recipe-salt-0-value')
            .to.have.value.that.equals(parseFloat(salt_vals[2]));

          browser
            .element("xpath", "//select[@id='edit-field-recipe-salt-0-status']/option[@selected='selected']", function (element) {
              browser.elementIdAttribute(element.value.ELEMENT, 'value', function (text) {
                browser.assert.equal(text.value, salt_vals[1].toLowerCase());
              })
            });
        }

        if (Object.keys(node.other_options).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-recipe-other-options-0-value')
            .to.have.value.which.contains(node.other_options);
        }

        browser
          .useCss()
          .expect.element('#edit-field-recipe-ingredients-0-value')
          .to.have.value.which.contains(node.ingredients);

        browser
          .useCss()
          .expect.element('#edit-field-recipe-method-0-value')
          .to.have.value.which.contains(node.method);

        browser
          .useCss()
          .expect.element('#edit-field-recipe-nutrition-info-0-value')
          .to.have.value.which.contains(node.method);

        if (Object.keys(node.allergy_advice).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-recipe-allergy-advice-0-value')
            .to.have.value.which.contains(node.allergy_advice);
        }

        if (Object.keys(node.food_safety).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-recipe-food-safety-0-value')
            .to.have.value.which.contains(node.food_safety);
        }
      });
  }
};
