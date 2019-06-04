module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether Media have been migrated': browser => {
        browser
            .drupalLogin({name: 'admin', password: 'letmein'});

        browser
            .drupalRelativeURL('/admin/content/media')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .to.be.present;
    },

    'Test whether any Image media files have been migrated': browser => {

        browser
            .drupalRelativeURL('/admin/content/media?name=&type=image&status=All&langcode=All')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .text.to.contain('Image');
    },

    'Test whether any Audio media have been migrated': browser => {

        browser
            .drupalRelativeURL('/admin/content/media?name=&type=audio&status=All&langcode=All')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .text.to.contain('Audio');
    },

    'Test whether any Remote Video media have been migrated': browser => {

        browser
            .drupalRelativeURL('/admin/content/files?filename=&filemime=application%2Fmsword&status=All')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .text.to.contain('Remote Video');
    },

    'Test whether any Document media have been migrated': browser => {

        browser
            .drupalRelativeURL('/admin/content/media?name=&type=document&status=All&langcode=All')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .text.to.contain('Document');
    },

    'Test for specific media file': browser => {

        browser
            .drupalRelativeURL('/admin/content/media?name=Outdoor+gym&type=image&status=All&langcode=All')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .text.to.contain('Image');
    }
};
