module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-recipe'],

  'Test whether Recipe content type exists': function (browser) {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/structure/types/manage/recipe')
      // The text match takes a regex, not a literal string.
      .expect.element('h1.page-title').text.to.match(/edit recipe content type/i);
  },
  'Test whether Recipe content type field exists (field_meta_tags)': browser => {
    browser
      .drupalRelativeURL('/admin/structure/types/manage/recipe/fields')
      .expect.element('#field-meta-tags').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_description)': browser => {
    browser.expect.element('#field-recipe-description').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_main_ingredient)': browser => {
    browser.expect.element('#field-recipe-main-ingredient').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_course_type)': browser => {
    browser.expect.element('#field-recipe-course-type').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_special_diet)': browser => {
    browser.expect.element('#field-recipe-special-diet').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_preptime)': browser => {
    browser.expect.element('#field-recipe-preptime').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_season)': browser => {
    browser.expect.element('#field-recipe-season').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_serves)': browser => {
    browser.expect.element('#field-recipe-serves').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_allergens)': browser => {
    browser.expect.element('#field-recipe-allergens').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_image)': browser => {
    browser.expect.element('#field-recipe-image').to.be.present;
  },
  // 'Test whether Recipe content type field exists (field_recipe_fat_content)': browser => {
  //   browser.expect.element('#field-recipe-fat-content').to.be.present;
  // },
  // 'Test whether Recipe content type field exists (field_recipe_saturates)': browser => {
  //   browser.expect.element('#field-recipe-saturates').to.be.present;
  // },
  // 'Test whether Recipe content type field exists (field_recipe_sugar)': browser => {
  //   browser.expect.element('#field-recipe-sugar').to.be.present;
  // },
  // 'Test whether Recipe content type field exists (field_recipe_salt)': browser => {
  //   browser.expect.element('#field-recipe-salt').to.be.present;
  // },
  'Test whether Recipe content type field exists (field_recipe_other_options)': browser => {
    browser.expect.element('#field-recipe-other-options').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_ingredients)': browser => {
    browser.expect.element('#field-recipe-ingredients').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_method)': browser => {
    browser.expect.element('#field-recipe-method').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_nutrition_info)': browser => {
    browser.expect.element('#field-recipe-nutrition-info').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_allergy_advice)': browser => {
    browser.expect.element('#field-recipe-allergy-advice').to.be.present;
  },
  'Test whether Recipe content type field exists (field_recipe_food_safety)': browser => {
    browser.expect.element('#field-recipe-food-safety').to.be.present;
  },
  'Test whether Recipe content type field exists (field_livelink_url)': browser => {
    browser.expect.element('#field-livelink-url').to.be.present;
  },
  'Test whether Recipe content type field exists (field_livelink_id)': browser => {
    browser.expect.element('#field-livelink-id').to.be.present;
  },
  'Test whether we have any migrated content for recipe nodes': browser => {
    browser
      .drupalLogin({ name: 'admin', password: 'letmein' })
      .drupalRelativeURL('/admin/content?type=recipe')
      .expect.element('#views-form-content-page-1 > table > tbody > tr')
      .text.to.not.contain('No content available');
  }
};
