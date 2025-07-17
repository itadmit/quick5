/**
 * מנהל מסד נתונים לסקשנים
 */
class SectionsDatabase {
    constructor(sectionsManager) {
        this.sectionsManager = sectionsManager;
    }

    /**
     * שמירת כל הסקשנים למסד הנתונים
     */
    async saveAllSections() {
        console.log('Saving all sections to database...');
        
        if (!this.sectionsManager.hasUnsavedChanges) {
            this.sectionsManager.customizer.showNotification('אין שינויים לשמירה', 'info');
            return;
        }

        try {
            // שמירת כל הסקשנים
            for (const section of this.sectionsManager.sections) {
                await this.saveSectionToDatabase(section);
            }

            // סימון שהשינויים נשמרו
            this.sectionsManager.markChangesSaved();
            
            this.sectionsManager.customizer.showNotification('השינויים נשמרו בהצלחה', 'success');
        } catch (error) {
            console.error('Error saving sections:', error);
            this.sectionsManager.customizer.showNotification('שגיאה בשמירת השינויים', 'error');
        }
    }

    /**
     * שמירת סקשן יחיד למסד הנתונים
     */
    async saveSectionToDatabase(sectionData) {
        console.log('Saving section to database:', sectionData);
        
        // סינון הגדרות שזהות לברירת מחדל
        const filteredData = this.filterDefaultSettings(sectionData);
        
        const response = await fetch('../api/save-section.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_section',
                section: filteredData
            })
        });

        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('API Error:', errorText);
            throw new Error('Failed to save section to database: ' + response.status);
        }

        return await response.json();
    }

    /**
     * סינון הגדרות שזהות לברירת מחדל
     */
    filterDefaultSettings(sectionData) {
        const defaults = this.sectionsManager.getDefaultSettings(sectionData.type);
        const customSettings = {};
        
        // השוואה והוספה רק של הגדרות שונות
        for (const [key, value] of Object.entries(sectionData.settings || {})) {
            if (!defaults.hasOwnProperty(key) || defaults[key] !== value) {
                customSettings[key] = value;
            }
        }
        
        return {
            ...sectionData,
            settings: customSettings
        };
    }

    /**
     * עדכון סקשן במסד הנתונים
     */
    async updateSectionInDatabase(sectionData) {
        console.log('Updating section in database:', sectionData);
        
        // סינון הגדרות שזהות לברירת מחדל
        const filteredData = this.filterDefaultSettings(sectionData);
        
        const response = await fetch('../api/save-section.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_section',
                section: filteredData
            })
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('API Error:', errorText);
            throw new Error('Failed to update section in database: ' + response.status);
        }

        return await response.json();
    }

    /**
     * מחיקת סקשן ממסד הנתונים
     */
    async deleteSectionFromDatabase(sectionId) {
        console.log('Deleting section from database:', sectionId);
        
        const response = await fetch('../api/save-section.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_section',
                section_id: sectionId
            })
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('API Error:', errorText);
            throw new Error('Failed to delete section from database: ' + response.status);
        }

        return await response.json();
    }

    /**
     * ביטול שינויים - טעינה מחדש מהמסד
     */
    async cancelChanges() {
        console.log('Canceling changes...');
        
        if (!this.sectionsManager.hasUnsavedChanges) {
            this.sectionsManager.customizer.showNotification('אין שינויים לביטול', 'info');
            return;
        }

        try {
            // טעינה מחדש מהמסד
            await this.sectionsManager.loadSections();
            
            // סימון שאין שינויים לא שמורים
            this.sectionsManager.markChangesSaved();
            
            this.sectionsManager.customizer.showNotification('השינויים בוטלו', 'success');
        } catch (error) {
            console.error('Error canceling changes:', error);
            this.sectionsManager.customizer.showNotification('שגיאה בביטול השינויים', 'error');
        }
    }
} 