import React from 'react';
import { MatchTree } from '../../shared/models/MatchTree';
import { FlexBracket } from '../../shared/components/Bracket/FlexBracket';

interface BracketPreviewProps {
	matchTree: MatchTree
}

export const BracketTemplatePreview = (props: BracketPreviewProps) => {
	const {
		matchTree
	} = props
	return (
		<div className='tw-flex tw-flex-col tw-justify-center tw-m-auto tw-max-w-[474px] tw-h-[296px]'>
			<FlexBracket
				matchTree={matchTree}
			/>
		</div>
	)
}