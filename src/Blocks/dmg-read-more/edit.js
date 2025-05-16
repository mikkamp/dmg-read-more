/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { Post, store } from '@wordpress/core-data';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import './editor.scss';

/**
 * Render block contents
 *
 * @param {number} readMoreId
 * @param {Post} post
 * @returns Block contents
 */
const renderContents = ( readMoreId, post ) => {
	if ( ! readMoreId ) {
		return __( 'Select a post...', 'dmg-read-more' );
	}

	if ( ! post ) {
		return __( 'Loading...', 'dmg-read-more' );
	}

	return (
		<>
			{ __( 'Read More: ', 'dmg-read-more' ) }
			<a href={ post.link }>{ post.title?.rendered }</a>
		</>
	);
};

/**
 * Structure of the block in the context of the editor
 *
 * @return {Element} Element to render
 */
export default function Edit( { attributes, setAttributes } ) {
	const { readMoreId } = attributes;

	// Fetch the post object from the data store
	const post = useSelect(
		( select ) =>
			readMoreId
				? select( store ).getEntityRecord(
						'postType',
						'post',
						readMoreId
				  )
				: null,
		[ readMoreId ]
	);

	const onChangeReadMore = ( newId ) => {
		setAttributes( { readMoreId: Number( newId ) } );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody>
					<TextControl
						label={ __( 'Read More post ID', 'dmg-read-more' ) }
						help={ __( 'Select a post', 'dmg-read-more' ) }
						value={ readMoreId }
						onChange={ onChangeReadMore }
					/>
				</PanelBody>
			</InspectorControls>

			<p { ...useBlockProps() }>{ renderContents( readMoreId, post ) }</p>
		</>
	);
}
