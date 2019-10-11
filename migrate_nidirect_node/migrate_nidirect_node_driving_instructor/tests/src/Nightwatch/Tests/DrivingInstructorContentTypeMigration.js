module.exports = {
    '@tags': ['nidirect-migrations-config'],

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
            .expect.element('#field-contact-phone').to.be.present;
        browser
            .expect.element('#field-meta-tags').to.be.present;
        browser
            .expect.element('#field-contact-mobile').to.be.present;
        browser
            .expect.element('#field-link-url').to.be.present;
    }

};
