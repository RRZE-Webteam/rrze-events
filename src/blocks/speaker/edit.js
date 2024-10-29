import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ComboboxControl, TextControl, RadioControl } from '@wordpress/components';
//import { ServerSideRender } from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import metadata from './block.json'; // Import block.json metadata


export default ({ attributes, setAttributes }) => {

    const blockProps = useBlockProps();
    const {numSpeakers } = attributes;
    const [layout, setLayout] = useState( attributes.layout || 'grid' );
    const [orderBy, setOrderBy] = useState( attributes.orderBy || 'lastname' );
    const [selectedCategories, setSelectedCategories] = useState(attributes.selectedCategories || []);
    const [selectedSpeakers, setSelectedSpeakers] = useState(attributes.selectedSpeakers || []);

    // Initialize attributes with default values from block.json
    const defaultAttributes = {};
    Object.keys(metadata.attributes).forEach(key => {
        defaultAttributes[key] = metadata.attributes[key].default;
    });

    useEffect(() => {
        // Set default attributes when the component mounts
        setAttributes(defaultAttributes);
    }, []);

    // Begriffe der Taxonomie abrufen (z. B. Kategorien)
    const categories = useSelect(select => {
        return select('core').getEntityRecords('taxonomy', 'speaker_category', { per_page: -1 });
    }, []);

    // Funktion zur Aktualisierung der Mehrfachauswahl
    const onAddCategory = (categoryId) => {
        if (!selectedCategories.includes(categoryId)) {
            const newCategories = [...selectedCategories, categoryId];
            setSelectedCategories(newCategories);
            setAttributes({ selectedCategories: newCategories });
        }
    };

    const onRemoveCategory = (categoryId) => {
        const newCategories = selectedCategories.filter(id => id !== categoryId);
        setSelectedCategories(newCategories);
        setAttributes({ selectedCategories: newCategories });
    };

    // Begriffe für die Combobox-Optionen aufbereiten
    const categoryOptions = categories ? categories.map(category => ({
        label: category.name,
        value: category.slug
    })) : [];

    // Posts abrufen
    const speakers = useSelect(select => {
        return select('core').getEntityRecords('postType', 'speaker');
    }, []);

    // Begriffe für die Combobox-Optionen aufbereiten
    const speakerOptions = speakers ? speakers.map(speaker => ({
        label: speaker.title.rendered,
        value: speaker.id
    })) : [];

    // Funktion zur Aktualisierung der Mehrfachauswahl
    const onAddSpeaker = (speakerId) => {
        if (!selectedSpeakers.includes(speakerId)) {
            const newSpeakers = [...selectedSpeakers, speakerId];
            setSelectedSpeakers(newSpeakers);
            setAttributes({ selectedSpeakers: newSpeakers });
        }
    };

    const onRemoveSpeaker = (speakerId) => {
        const newSpeakers = selectedSpeakers.filter(id => id !== speakerId);
        setSelectedSpeakers(newSpeakers);
        setAttributes({ selectedSpeakers: newSpeakers });
    };

    // Funktion zur Aktualisierung der numerischen Eingabe
    const onChangeNumber = (value) => {
        // Sicherstellen, dass nur Zahlen gespeichert werden
        const newNumber = parseInt(value, 10);
        if (!isNaN(newNumber) && newNumber >= -1) {
            setAttributes({ numSpeakers: newNumber });
        } else {
            setAttributes({ numSpeakers: '' });
        }
    };

    const onChangeLayout = (value) => {
        setLayout( value );
        setAttributes({Layout: value});
    };
    const onChangeOrderBy = (value) => {
        setOrderBy( value );
        setAttributes({orderBy: value});
    };

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('Layout', 'rrze-events')}>
                    <RadioControl
                        label={__('Layout', 'rrze-events')}
                        selected={ layout }
                        options={ [
                            { label: __('Grid', 'rrze-events'), value: 'grid' },
                            { label: __('List', 'rrze-events'), value: 'list' },
                        ] }
                        onChange={onChangeLayout}
                    />
                    <RadioControl
                        label={__('Order', 'rrze-events')}
                        selected={ orderBy }
                        options={ [
                            { label: __('Last Name', 'rrze-events'), value: 'lastname' },
                            { label: __('First Name', 'rrze-events'), value: 'firstname' },
                        ] }
                        onChange={onChangeOrderBy}
                    />
                </PanelBody>
                <PanelBody title={__('Select Speakers', 'rrze-events')}>
                    <TextControl
                        label={__('Count', 'rrze-events')}
                        type="number"
                        value={numSpeakers}
                        onChange={onChangeNumber}
                        help={__('How many speakers do you want to show? Enter -1 for all speakers.', 'rrze-events')}
                    />
                    <hr/>
                    <ComboboxControl
                        label={__('Categories', 'rrze-events')}
                        options={categoryOptions}
                        onChange={onAddCategory}
                    />
                    <div style={{marginTop: '10px'}}>
                        <strong>{__('Selected Categories', 'rrze-events')}:</strong>
                        <ul>
                            {selectedCategories.map(categorySlug => {
                                const category = categories.find(t => t.slug === categorySlug);
                                return (
                                    <li key={categorySlug}>
                                        {category?.name}
                                        <button onClick={() => onRemoveCategory(categorySlug)} style={{marginLeft: '5px'}}>
                                            {__('Remove', 'rrze-events')}
                                        </button>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                    <hr/>
                    <ComboboxControl
                        label={__('Speakers', 'rrze-events')}
                        options={speakerOptions}
                        onChange={onAddSpeaker}
                    />
                    <div style={{marginTop: '10px'}}>
                        <strong>{__('Selected Speakers', 'rrze-events')}:</strong>
                        <ul>
                            {selectedSpeakers.map(speakerId => {
                                const speaker = speakers.find(t => t.id === speakerId);
                                return (
                                    <li key={speakerId}>
                                        {speaker?.title.rendered}
                                        <button onClick={() => onRemoveSpeaker(speakerId)} style={{marginLeft: '5px'}}>
                                            {__('Remove', 'rrze-events')}
                                        </button>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                </PanelBody>
                </InspectorControls>
            <p>{__('Selected Categories', 'rrze-events')}: {selectedCategories.join(', ')}<br/>
                Eingegebene Zahl: {numSpeakers}</p>
        </div>
    );
};
