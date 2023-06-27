
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
import { nextPage, setPage, selectCurrentPage, selectNumPages } from '../../../features/bracket_nav/bracketNavSlice';

import { MatchTree } from '../../../bracket/models/MatchTree';
import { BracketRes } from '../../../api/types/bracket';
import { bracketConstants } from '../../../bracket/constants';
import { PaginatedLandingPage } from './PaginatedLandingPage';
import { PaginatedRound } from './PaginatedRound'
import { PaginatedBracketResult } from './PaginatedBracketResult'

import { NavButton } from '../../../bracket/components/PaginatedBracket';
import { useDomContentLoaded } from '../../../utils/hooks';

interface PaginatedBracketProps {
	matchTree?: MatchTree;
}

export const PaginatedBracket = (props: PaginatedBracketProps) => {
	const {
		matchTree,
	} = props;

	if (!matchTree) {
		return <></>
	}

	const currentPage = useAppSelector(selectCurrentPage)
	const numPages = useAppSelector(selectNumPages)
	const dispatch = useAppDispatch()
	const goNext = () => dispatch(nextPage())

	const handleStart = () => {
		console.log('current page', currentPage)
		goNext()
	}

	const getPage = () => {
		if (currentPage <= 0) {
			return <PaginatedLandingPage onStart={handleStart} />
		} else if (currentPage < numPages) {
			return <PaginatedRound />
		} else {
			return <PaginatedBracketResult />
		}
	}

	return (
		<div className={`wpbb-paginated-bracket wpbb-dark-mode`}>
			{getPage()}
		</div>
	)
}