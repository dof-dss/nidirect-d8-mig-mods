module.exports = {
    '@tags': ['nidirect-migrations', 'nidirect-media'],

    'Test whether Media have been migrated': browser => {
        browser
            .drupalLogin({ name: 'admin', password: 'letmein' });

        browser
            .drupalRelativeURL('/admin/content/media')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .to.be.present;
    },

    'Test whether any Image media files have been migrated': browser => {

        browser
            .drupalRelativeURL('/admin/content/media?name=&type=image&status=All&langcode=All')
            .expect.element('table > tbody > tr > td:nth-child(4)')
            .text.to.contain('Image');
    },

    'Test whether any Audio media have been migrated': browser => {

        browser
            .drupalRelativeURL('/admin/content/media?name=&type=audio&status=All&langcode=All')
            .expect.element('table > tbody > tr > td:nth-child(4)')
            .text.to.contain('Audio');
    },

    'Test whether any Remote Video media have been migrated': browser => {

        browser
            .drupalRelativeURL('/admin/content/media?name=&type=remote_video&status=All&langcode=All')
            .expect.element('table > tbody > tr > td:nth-child(4)')
            .text.to.contain('Remote video');
    },

    'Test whether any Document media have been migrated': browser => {

        browser
            .drupalRelativeURL('/admin/content/media?name=&type=document&status=All&langcode=All')
            .expect.element('table > tbody > tr > td:nth-child(4)')
            .text.to.contain('Document');
    },

    'Test for specific media file (brown trout - Image)': browser => {

        browser
            .drupalRelativeURL('/admin/content/media?name=Enjoy-fishing-for-brown-trout&type=All&status=All&langcode=All')
            .expect.element('table > tbody > tr > td:nth-child(4)')
            .text.to.contain('Image');
    },

    'Test for specific media file (Samson & Banana - Remote Video)': browser => {

        browser
            .drupalRelativeURL('/admin/content/media?name=Samson+%26+Banana&type=remote_video&status=All&langcode=All')
            .expect.element('table > tbody > tr > td:nth-child(4)')
            .text.to.contain('Remote video');
    }
};
