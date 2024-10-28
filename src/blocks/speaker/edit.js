import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ComboboxControl, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';

export default ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps();

    // State für die Mehrfachauswahl
    const [selectedTerms, setSelectedTerms] = useState(attributes.selectedTerms || []);

    // Begriffe der Taxonomie abrufen (z. B. Kategorien)
    const terms = useSelect(select => {
        return select('core').getEntityRecords('taxonomy', 'speaker_category', { per_page: -1 });
    }, []);

    console.log(terms);

    // Funktion zur Aktualisierung der Mehrfachauswahl
    const onAddTerm = (termId) => {
        if (!selectedTerms.includes(termId)) {
            const newTerms = [...selectedTerms, termId];
            setSelectedTerms(newTerms);
            setAttributes({ selectedTerms: newTerms });
        }
    };

    const onRemoveTerm = (termId) => {
        const newTerms = selectedTerms.filter(id => id !== termId);
        setSelectedTerms(newTerms);
        setAttributes({ selectedTerms: newTerms });
    };

    // Begriffe für die Combobox-Optionen aufbereiten
    const termOptions = terms ? terms.map(term => ({
        label: term.name,
        value: term.id
    })) : [];

    // State für die Mehrfachauswahl
    const [selectedSpeakers, setSelectedSpeakers] = useState(attributes.selectedSpeakers || []);

    // Begriffe der Taxonomie abrufen (z. B. Kategorien)
    const speakers = useSelect(select => {
        return select('core').getEntityRecords('postType', 'speaker');
    }, []);
console.log(speakers);
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

    const { numberValue } = attributes;

    // Funktion zur Aktualisierung der numerischen Eingabe
    const onChangeNumber = (value) => {
        // Sicherstellen, dass nur Zahlen gespeichert werden
        const number = parseInt(value, 10);
        if (!isNaN(number) && number >= -1) {
            setAttributes({ numberValue: number });
        } else {
            setAttributes({ numberValue: '' });
        }
    };


    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('Select Speakers', 'rrze-events')}>
                    <TextControl
                        label={__('Count', 'rrze-events')}
                        type="number"
                        value={numberValue}
                        onChange={onChangeNumber}
                        help={ __('How many speakers do you want to show? Enter -1 for all speakers.', 'rrze-events') }
                    />
                    <hr/>
                    <ComboboxControl
                        label={__('Categories', 'rrze-events')}
                        options={termOptions}
                        onChange={onAddTerm}
                    />
                    <div style={{marginTop: '10px'}}>
                        <strong>{__('Selected Categories', 'rrze-events')}:</strong>
                        <ul>
                            {selectedTerms.map(termId => {
                                const term = terms.find(t => t.id === termId);
                                return (
                                    <li key={termId}>
                                        {term?.name}
                                        <button onClick={() => onRemoveTerm(termId)} style={{marginLeft: '5px'}}>
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
            <p>{__('Selected Categories', 'rrze-events')}: {selectedTerms.join(', ')}<br/>
                Eingegebene Zahl: {numberValue}</p>
        </div>
    );
};
