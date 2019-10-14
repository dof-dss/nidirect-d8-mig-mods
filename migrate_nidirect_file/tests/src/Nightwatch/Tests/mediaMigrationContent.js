module.exports = {
    '@tags': ['nidirect-migrations-content'],

    'Test for specific media file (brown trout - Image)': browser => {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS });

        browser
            .drupalRelativeURL('/admin/content/media?name=Enjoy-fishing-for-brown-trout&type=All&status=All&langcode=All')
            .expect.element('#edit_type_chosen > a > span')
            .text.to.contain('Image');
    },

    'Test for specific media file (Samson & Banana - Remote Video)': browser => {

        browser
            .drupalRelativeURL('/admin/content/media?name=Samson+%26+Banana&type=remote_video&status=All&langcode=All')
            .expect.element('#edit_type_chosen > a > span')
            .text.to.contain('Remote video');
    }
};
