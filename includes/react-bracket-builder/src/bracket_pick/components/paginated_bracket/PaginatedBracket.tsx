
import React, { useState, useEffect } from 'react';
import * as Sentry from '@sentry/react';
import { bracketApi } from '../../../api/bracketApi';
import { useWindowDimensions } from '../../../utils/hooks';
import Spinner from 'react-bootstrap/Spinner'
// import { Bracket } from '../../bracket/components/Bracket';
// import { Bracket } from '../../bracket/components/Bracket';
import { PairedBracket } from '../../../bracket/components/PairedBracket';
import { useAppSelector, useAppDispatch } from '../../../app/hooks';
import { setMatchTree, selectMatchTree } from '../../../features/match_tree/matchTreeSlice';
import { nextPage, setPage, selectCurrentPage } from '../../../features/bracket_nav/bracketNavSlice';

import { MatchTree } from '../../../bracket/models/MatchTree';
import { BracketRes } from '../../../api/types/bracket';
import { bracketConstants } from '../../../bracket/constants';
import { PaginatedLandingPage } from './PaginatedLandingPage';

import { NavButton } from '../../../bracket/components/PaginatedBracket';
import { useDomContentLoaded } from '../../../utils/hooks';

export const PaginatedBracket = (bracketProps) => {
	if (!bracketProps.matchTree) {
		return <></>
	}

	const currentPage = useAppSelector(selectCurrentPage)
	const dispatch = useAppDispatch()
	const goNext = () => dispatch(nextPage())
	// const nextPage = dispatch()

	const handleStart = () => {
		console.log('current page', currentPage)
		goNext()
	}

	return (
		<div className={`wpbb-paginated-bracket wpbb-dark-mode`}>
			<PaginatedLandingPage onStart={handleStart} />
		</div>
	)
}