import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ComboboxControl, TextControl, RadioControl, DatePicker } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import metadata from './block.json';

import './editor.scss';


export default ({ attributes, setAttributes }) => {

    const blockProps = useBlockProps();
    const { numTalks } = attributes;
    const { talkDate } = attributes;
    const [layout, setLayout] = useState( attributes.layout || 'grid' );
    const [orderBy, setOrderBy] = useState( attributes.orderBy || 'lastname' );
    const [selectedCategories, setSelectedCategories] = useState(attributes.selectedCategories || []);
    const [selectedTags, setSelectedTags] = useState(attributes.selectedTags || []);
    const [selectedTalks, setSelectedTalks] = useState(attributes.selectedTalks || []);

    // Initialize attributes with default values from block.json
    const defaultAttributes = {};
    Object.keys(metadata.attributes).forEach(key => {
        defaultAttributes[key] = metadata.attributes[key].default;
    });

    useEffect(() => {
        // Set default attributes when the component mounts
        setAttributes(defaultAttributes);
    }, []);

    // Category Settings
    const categories = useSelect(select => {
        return select('core').getEntityRecords('taxonomy', 'talk_category', { per_page: -1 }) || [];
    }, []);

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

    const categoryOptions = categories ? categories.map(category => ({
        label: category.name,
        value: category.slug
    })) : [];

    // Tag Settings
    const tags = useSelect(select => {
        return select('core').getEntityRecords('taxonomy', 'talk_tag', { per_page: -1 }) || [];
    }, []);

    const onAddTag = (tagId) => {
        if (!selectedTags.includes(tagId)) {
            const newTags = [...selectedTags, tagId];
            setSelectedTags(newTags);
            setAttributes({ selectedTags: newTags });
        }
    };

    const onRemoveTag = (tagId) => {
        const newTags = selectedTags.filter(id => id !== tagId);
        setSelectedTags(newTags);
        setAttributes({ selectedTags: newTags });
    };

    const tagOptions = tags ? tags.map(tag => ({
        label: tag.name,
        value: tag.slug
    })) : [];

    // Talk Settings
    const talks = useSelect(select => {
        return select('core').getEntityRecords('postType', 'talk') || [];
    }, []);

    const talkOptions = talks ? talks.map(talk => ({
        label: talk.title.rendered,
        value: talk.id
    })) : [];

    const onAddTalk = (talkId) => {
        if (!selectedTalks.includes(talkId)) {
            const newTalks = [...selectedTalks, talkId];
            setSelectedTalks(newTalks);
            setAttributes({ selectedTalks: newTalks });
        }
    };

    const onRemoveTalk = (talkId) => {
        const newTalks = selectedTalks.filter(id => id !== talkId);
        setSelectedTalks(newTalks);
        setAttributes({ selectedTalks: newTalks });
    };

    // Number Settings
    const onChangeNumber = (value) => {
        // Sicherstellen, dass nur Zahlen gespeichert werden
        const newNumber = parseInt(value, 10);
        if (!isNaN(newNumber) && newNumber >= -1) {
            setAttributes({ numTalks: newNumber });
        } else {
            setAttributes({ numTalks: '' });
        }
    };

    // Other Settings
    const onChangeLayout = (value) => {
        setLayout( value );
        setAttributes({layout: value});
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
                            { label: __('Table', 'rrze-events'), value: 'table' },
                            { label: __('Short', 'rrze-events'), value: 'short' },
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
                <PanelBody title={__('Select Talks', 'rrze-events')}>
                    <TextControl
                        label={__('Count', 'rrze-events')}
                        type="number"
                        value={numTalks}
                        onChange={onChangeNumber}
                        help={__('How many talks do you want to show? Enter -1 for all talks.', 'rrze-events')}
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
                        label={__('Tags', 'rrze-events')}
                        options={tagOptions}
                        onChange={onAddTag}
                    />
                    <div style={{marginTop: '10px'}}>
                        <strong>{__('Selected Tags', 'rrze-events')}:</strong>
                        <ul>
                            {selectedTags.map(tagSlug => {
                                const tag = tags.find(t => t.slug === tagSlug);
                                return (
                                    <li key={tagSlug}>
                                        {tag?.name}
                                        <button onClick={() => onRemoveTag(tagSlug)} style={{marginLeft: '5px'}}>
                                            {__('Remove', 'rrze-events')}
                                        </button>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                    <hr/>
                    <ComboboxControl
                        label={__('Talks', 'rrze-events')}
                        options={talkOptions}
                        onChange={onAddTalk}
                    />
                    <div style={{marginTop: '10px'}}>
                        <strong>{__('Selected Talks', 'rrze-events')}:</strong>
                        <ul>
                            {selectedTalks.map(talkId => {
                                const talk = talks.find(t => t.id === talkId);
                                return (
                                    <li key={talkId}>
                                        {talk?.title.rendered}
                                        <button onClick={() => onRemoveTalk(talkId)} style={{marginLeft: '5px'}}>
                                            {__('Remove', 'rrze-events')}
                                        </button>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                    <DatePicker
                        currentDate={ talkDate }
                        onChange={ ( newDate ) => setDate( newDate ) }
                    />
                </PanelBody>
            </InspectorControls>
            <ServerSideRender
                block="rrze/events-talk"
                attributes={attributes}
            />
        </div>
    );
};
