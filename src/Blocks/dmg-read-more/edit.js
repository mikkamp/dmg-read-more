/**
 * WordPress dependencies
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { useEntityRecord } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import PostSelector from './post-selector';

/**
 * Structure of the block in the context of the editor
 *
 * @param {Object} props Block properties
 * @return {Element} Element to render
 */
export default function Edit( props ) {
	const { readMoreId } = props.attributes;
	const { hasResolved, record: post = null } = useEntityRecord(
		'postType',
		'post',
		readMoreId
	);

	/**
	 * Render the read more content
	 *
	 * @return {Element} Rendered content
	 */
	const readMoreContent = () => {
		if ( ! readMoreId ) {
			return __( 'Select a post…', 'dmg-read-more' );
		}

		if ( readMoreId > 0 && ! hasResolved ) {
			return __( 'Loading…', 'dmg-read-more' );
		}

		if ( readMoreId > 0 && hasResolved && ! post ) {
			return __(
				'Not found, please select another post',
				'dmg-read-more'
			);
		}

		return (
			<>
				{ __( 'Read More: ', 'dmg-read-more' ) }
				<a href={ post.link }>{ post.title?.rendered }</a>
			</>
		);
	};

	return (
		<>
			<InspectorControls>
				<PanelBody>
					<PostSelector { ...props } />
				</PanelBody>
			</InspectorControls>

			<p { ...useBlockProps( { className: 'dmg-read-more' } ) }>
				{ readMoreContent() }
			</p>
		</>
	);
}
