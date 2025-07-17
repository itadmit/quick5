<?php
/**
 * Section Width Helper - פונקציות גלובליות לטיפול ברוחב סקשנים
 * 
 * קובץ זה מכיל פונקציות שמשמשות כל הסקשנים בבילדר
 * לטיפול אחיד ברוחב הסקשנים
 */

class SectionWidthHelper {
    
    /**
     * מחזיר את ה-CSS class עבור רוחב הסקשן
     */
    public static function getSectionWidthClass($data) {
        $width = $data['width'] ?? 'container';
        
        switch ($width) {
            case 'container':
                return 'section-width-container';
            case 'full':
                return 'section-width-full';
            case 'custom':
                return 'section-width-custom';
            default:
                return 'section-width-container';
        }
    }
    
    /**
     * מחזיר את הסגנון המותאם אישית עבור רוחב custom
     */
    public static function getSectionWidthStyle($data) {
        if (($data['width'] ?? 'container') === 'custom') {
            $customWidth = $data['customWidth'] ?? 800;
            $customUnit = $data['customWidthUnit'] ?? 'px';
            return "max-width: {$customWidth}{$customUnit};";
        }
        return '';
    }
    
    /**
     * מחזיר HTML attributes מלאים לסקשן
     */
    public static function getSectionWidthAttributes($data) {
        $class = self::getSectionWidthClass($data);
        $style = self::getSectionWidthStyle($data);
        
        $attributes = 'class="' . $class . '"';
        if (!empty($style)) {
            $attributes .= ' style="' . $style . '"';
        }
        
        return $attributes;
    }
    
    /**
     * מחזיר את הגדרות הרוחב לשימוש ב-JavaScript
     */
    public static function getWidthSettingsForJS() {
        return [
            'container' => [
                'label' => 'קונטיינר (1200px)',
                'value' => 'container'
            ],
            'full' => [
                'label' => 'רוחב מלא',
                'value' => 'full'
            ],
            'custom' => [
                'label' => 'רוחב מותאם אישית',
                'value' => 'custom'
            ]
        ];
    }
    
    /**
     * מחזיר הגדרות ברירת מחדל לרוחב
     */
    public static function getDefaultWidthSettings() {
        return [
            'width' => 'container',
            'customWidth' => 800,
            'customWidthUnit' => 'px'
        ];
    }
}

/**
 * פונקציות עזר קצרות לשימוש מהיר
 */
function getSectionWidthClass($data) {
    return SectionWidthHelper::getSectionWidthClass($data);
}

function getSectionWidthStyle($data) {
    return SectionWidthHelper::getSectionWidthStyle($data);
}

function getSectionWidthAttributes($data) {
    return SectionWidthHelper::getSectionWidthAttributes($data);
}
?> 