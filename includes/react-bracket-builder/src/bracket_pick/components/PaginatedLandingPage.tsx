import React, { useState, useEffect } from 'react';
import * as Sentry from '@sentry/react';
import { bracketApi } from '../../api/bracketApi';
import { useWindowDimensions } from '../../utils/hooks';
import Spinner from 'react-bootstrap/Spinner'
// import { Bracket } from '../../bracket/components/Bracket';
// import { Bracket } from '../../bracket/components/Bracket';
import { PairedBracket } from '../../bracket/components/PairedBracket';
import { PaginatedBracket } from '../../bracket/components/PaginatedBracket';
import { useAppSelector, useAppDispatch } from '../../app/hooks';
import { setMatchTree, selectMatchTree } from '../../features/match_tree/matchTreeSlice';

import { MatchTree } from '../../bracket/models/MatchTree';
import { BracketRes } from '../../api/types/bracket';
import { bracketConstants } from '../../bracket/constants';

import { NavButton } from '../../bracket/components/PaginatedBracket';
import { useDomContentLoaded } from '../../utils/hooks';

export const PaginatedLandingPage = (bracketProps) => {
	if (!bracketProps.matchTree) {
		return <></>
	}

	const [bracketScale, setBracketScale] = useState(1);
	const domContentLoaded = useDomContentLoaded();

	useEffect(() => {
		if (domContentLoaded) {
			console.log('dom content loaded')
			const numLines = countLineToElements();
			console.log('numLines', numLines)
			//wait for 3 seconds for lines to render
			setTimeout(() => {
				const numLines = countLineToElements();
				console.log('numLines', numLines)
				const numBracketLines = countBracketLineElements();
				console.log('numBracketLines', numBracketLines)
				if (numLines > 0) {
					setBracketScale(.5);
				}
			}, 3000)

			// setBracketScale(.5);
		} else {
			console.log('dom content not loaded')
		}
	}, [domContentLoaded])


	return (
		<div className={`wpbb-paginated-landing-page wpbb-dark-mode`}>
			<div className={'wpbb-slogan-container'}>
				<span className={'wpbb-slogan-text'}>WHO YOU GOT?</span>
			</div>
			<div className='wpbb-paired-bracket-container' style={{ transform: `scale(${bracketScale})` }}>
				<PairedBracket {...bracketProps} />
			</div>
		</div>
	)
}

function countLineToElements() {
	const elements = document.getElementsByClassName('react-lineto-placeholder');
	return elements.length;
}

function countBracketLineElements() {
	const elements = document.getElementsByClassName('wpbb-bracket-line');
	return elements.length;
}