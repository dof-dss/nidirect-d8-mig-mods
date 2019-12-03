module.exports = {
  '@tags': [
    'nidirect',
    'nidirect_config',
    'nidirect_config_driving_instructor',
  ],

    'Test whether Driving Instructor content type exists': browser => {
        browser
            .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Driving instructor');
    },

    'Test whether Driving Instructor fields exist': browser => {

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor/fields')
            .expect.element('#field-di-adi-no').to.be.present;
        browser
            .expect.element('#field-di-areas').to.be.present;
        browser
            .expect.element('#field-di-categories').to.be.present;
        browser
            .expect.element('#field-email-address').to.be.present;
        browser
            .expect.element('#field-di-firstname').to.be.present;
        browser
            .expect.element('#field-di-lastname').to.be.present;
        browser
            .expect.element('#field-telephone').to.be.present;
        browser
            .expect.element('#field-meta-tags').to.be.present;
        browser
            .expect.element('#field-link-url').to.be.present;
    },

    'Test whether Driving Instructor nodes exist': browser => {

        // See if we have any article nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=driving_instructor')
            .waitForElementVisible('body', 1000)
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Driving instructor');
    }
};
