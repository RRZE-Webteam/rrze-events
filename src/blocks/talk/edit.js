import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ComboboxControl, TextControl, ToggleControl, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import metadata from './block.json';

import './editor.scss';


export default ({ attributes, setAttributes }) => {

    const blockProps = useBlockProps();
    const { numTalks } = attributes;
    const { talkDate } = attributes;
    const { showImage } = attributes;
    const { showOrganisation } = attributes;
    const [tableColumns, setTableColumns] = useState(attributes.tableColumns || []);
    const [layout, setLayout] = useState( attributes.layout || 'grid' );
    const [orderBy, setOrderBy] = useState( attributes.orderBy || 'date' );
    const [orderType, setOrderType] = useState( attributes.orderType || 'ASC' );
    const [selectedCategories, setSelectedCategories] = useState(attributes.selectedCategories || []);
    const [selectedTags, setSelectedTags] = useState(attributes.selectedTags || []);
    const [selectedTalks, setSelectedTalks] = useState(attributes.selectedTalks || []);

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

    // Columns Settings

    const columns = [
        {label: __('Date', 'rrze-events'), value: 'date'},
        {label: __('Start Time', 'rrze-events'), value: 'start'},
        {label: __('End Time', 'rrze-events'), value: 'end'},
        {label: __('Start-End', 'rrze-events'), value: 'duration'},
        {label: __('Location', 'rrze-events'), value: 'location'},
        {label: __('Title', 'rrze-events'), value: 'title'},
        {label: __('Speaker', 'rrze-events'), value: 'speaker'},
        {label: __('Participants', 'rrze-events'), value: 'participants'},
        {label: __('Available Places', 'rrze-events'), value: 'available'},
        {label: __('Code', 'rrze-events'), value: 'short'},
    ];

    const onAddColumn = (columnKey) => {
        if (!tableColumns.includes(columnKey)) {
            const newColumns = [...tableColumns, columnKey];
            setTableColumns(newColumns);
            setAttributes({ tableColumns: newColumns });
        }
    };

    const onRemoveColumn = (columnKey) => {
        const newColumns = tableColumns.filter(value => value !== columnKey);
        setTableColumns(newColumns);
        setAttributes({ tableColumns: newColumns });
    };

    const onMoveColumnUp = (columnKey) => {
        //console.log(tableColumns, columnKey, tableColumns.indexOf(columnKey));
        const newColumnUp = move(tableColumns, tableColumns.indexOf(columnKey), tableColumns.indexOf(columnKey) - 1);
        //console.log(newColumnUp, columnKey, newColumnUp.indexOf(columnKey));
        setTableColumns(newColumnUp);
        setAttributes({ tableColumns: newColumnUp });
        //console.log(attributes);
    };

    const onMoveColumnDown = (columnKey) => {
        const newColumnDown = move(tableColumns, tableColumns.indexOf(columnKey), tableColumns.indexOf(columnKey) + 1);
        setTableColumns(newColumnDown);
        setAttributes({ tableColumns: newColumnDown });
    };

    function move(array, from, to) {
        if( to === from ) return array;
        const newArray = [...array];
        const target = newArray[from];
        const increment = to < from ? -1 : 1;
        for(let k = from; k !== to; k += increment){
            newArray[k] = newArray[k + increment];
        }
        newArray[to] = target;
        return newArray;
    }

    // Other Settings
    const onChangeLayout = (value) => {
        setLayout( value );
        setAttributes({layout: value});
    };
    const onChangeOrderBy = (value) => {
        setOrderBy( value );
        setAttributes({orderBy: value});
    };

    const onChangeOrderType = (value) => {
        setOrderType( value );
        setAttributes({orderType: value});
    };

    const onChangeDate = (value) => {
        setAttributes({talkDate: value});
    };
    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('Layout', 'rrze-events')}>
                    <SelectControl
                        label={__('Layout', 'rrze-events')}
                        value={layout}
                        options={[
                            {label: __('Grid', 'rrze-events'), value: 'grid'},
                            {label: __('Table', 'rrze-events'), value: 'table'},
                            {label: __('Short', 'rrze-events'), value: 'short'}
                        ]}
                        onChange={onChangeLayout}
                    />
                    {layout === "table" && (
                        <>
                        <ComboboxControl
                            label={__('Columns', 'rrze-events')}
                            options={columns}
                            onChange={onAddColumn}
                        />
                        <div style={{marginTop: '10px'}}>
                            {__('Selected Columns', 'rrze-events')}:
                            <ul className="selected-columns">
                                {tableColumns.map(columnSlug => {
                                    const column = columns.find(t => t.value === columnSlug);
                                    return (
                                        <li key={columnSlug}>
                                            {column?.label}
                                            <span className="buttons">
                                                <button onClick={() => onMoveColumnUp(columnSlug)}
                                                        style={{marginLeft: '5px'}}
                                                        aria-describedby={columnSlug + "_button_up"}
                                                        title={__('Up', 'rrze-events')}
                                                        className="button-up">
                                                    <svg height="18" viewBox="0 0 512 512" width="18" xmlns="http://www.w3.org/2000/svg"><g><polygon points="402.8,361.2 256,214.4 109.2,361.2 66.8,318.8 256,129.6 445.2,318.8  "/></g></svg>
                                                    <span className="screen-reader-text sr-only"
                                                        id={columnSlug + "_button_up"}>
                                                        {__('Up', 'rrze-events')}
                                                    </span>
                                                </button>
                                                <button onClick={() => onMoveColumnDown(columnSlug)}
                                                        aria-describedby={columnSlug + "_button_down"}
                                                        style={{marginLeft: '5px'}}
                                                        title={__('Down', 'rrze-events')}
                                                        className="button-down">
                                                    <svg height="18" viewBox="0 0 512 512" width="18" xmlns="http://www.w3.org/2000/svg"><g><polygon points="256,382.4 66.8,193.2 109.2,150.8 256,297.6 402.8,150.8 445.2,193.2  "/></g></svg>
                                                    <span className="screen-reader-text sr-only"
                                                          id={columnSlug + "_button_down"}>
                                                        {__('Down', 'rrze-events')}
                                                    </span>
                                                </button>
                                                <button onClick={() => onRemoveColumn(columnSlug)}
                                                        aria-describedby={columnSlug + "_button_remove"}
                                                        style={{marginLeft: '5px'}}
                                                        title={__('Remove', 'rrze-events')}
                                                        className="button-remove">
                                                    <svg height="18" viewBox="0 0 512 512" width="18" xmlns="http://www.w3.org/2000/svg"><polygon points="445.2,109.2 402.8,66.8 256,213.6 109.2,66.8 66.8,109.2 213.6,256 66.8,402.8 109.2,445.2 256,298.4 402.8,445.2   445.2,402.8 298.4,256 "/></svg>
                                                    <span className="screen-reader-text sr-only"
                                                          id={columnSlug + "_button_up"}>
                                                        {__('Remove', 'rrze-events')}
                                                    </span>
                                                </button>
                                            </span>
                                        </li>
                                    );
                                })}
                            </ul>
                            <hr/>
                        </div>
                        </>
                    )}
                    {layout === "grid" && (
                        <>
                        <ToggleControl
                            __nextHasNoMarginBottom
                            checked={!!showImage}
                            label={__('Show Talk Image', 'rrze-events')}
                            onChange={() =>
                                setAttributes({
                                    showImage: !showImage,
                                })
                            }
                        />
                        <ToggleControl
                            __nextHasNoMarginBottom
                            checked={!!showOrganisation}
                            label={__('Show Speaker Organisation', 'rrze-events')}
                            onChange={() =>
                                setAttributes({
                                    showOrganisation: !showOrganisation,
                                })
                            }
                        />
                        </>
                    )}
                    {(layout === "grid" || layout === "short") && (
                        <hr />
                    )}
                    <SelectControl
                        label={__('Order By', 'rrze-events')}
                        selected={orderBy}
                        options={[
                            {label: __('Date', 'rrze-events'), value: 'date'},
                            {label: __('Start Time', 'rrze-events'), value: 'start'},
                            {label: __('Title', 'rrze-events'), value: 'title'},
                            {label: __('Code', 'rrze-events'), value: 'shortname'}
                        ]}
                        onChange={onChangeOrderBy}
                    />
                    <SelectControl
                        label={__('Order', 'rrze-events')}
                        selected={orderType}
                        options={[
                            {label: __('ascending', 'rrze-events'), value: 'ASC'},
                            {label: __('descending', 'rrze-events'), value: 'DESC'}
                        ]}
                        onChange={onChangeOrderType}
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
                        {__('Selected Categories', 'rrze-events')}:
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
                        {__('Selected Tags', 'rrze-events')}:
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
                        {__('Selected Talks', 'rrze-events')}:
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
                    <hr />
                    <TextControl
                        label={__('Date', 'rrze-events')}
                        type="date"
                        value={talkDate}
                        onChange={onChangeDate}
                        help={__('Only show talks of one day.', 'rrze-events')}
                    />
                </PanelBody>
            </InspectorControls>
            <ServerSideRender
                block="rrze-events/talk"
                attributes={attributes}
            />
        </div>
    );
};
