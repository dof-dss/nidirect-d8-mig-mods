module.exports = {
  '@tags': ['nidirect-migrations-config'],

  'Test whether Recipe content type exists': function (browser) {
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/structure/types/manage/recipe')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit recipe content type/i);
  },
  'Test whether Recipe content type fields exist': browser => {
    browser.drupalRelativeURL('/admin/structure/types/manage/recipe/fields');
    browser.expect.element('#field-meta-tags').to.be.present;
    browser.expect.element('#field-recipe-description').to.be.present;
    browser.expect.element('#field-recipe-main-ingredient').to.be.present;
    browser.expect.element('#field-recipe-course-type').to.be.present;
    browser.expect.element('#field-recipe-special-diet').to.be.present;
    browser.expect.element('#field-recipe-preptime').to.be.present;
    browser.expect.element('#field-recipe-season').to.be.present;
    browser.expect.element('#field-recipe-serves').to.be.present;
    browser.expect.element('#field-recipe-allergens').to.be.present;
    browser.expect.element('#field-recipe-image').to.be.present;
    browser.expect.element('#field-recipe-fat-content').to.be.present;
    browser.expect.element('#field-recipe-saturates').to.be.present;
    browser.expect.element('#field-recipe-sugar').to.be.present;
    browser.expect.element('#field-recipe-salt').to.be.present;
    browser.expect.element('#field-recipe-other-options').to.be.present;
    browser.expect.element('#field-recipe-ingredients').to.be.present;
    browser.expect.element('#field-recipe-method').to.be.present;
    browser.expect.element('#field-recipe-nutrition-info').to.be.present;
    browser.expect.element('#field-recipe-allergy-advice').to.be.present;
    browser.expect.element('#field-recipe-food-safety').to.be.present;
  }
};
