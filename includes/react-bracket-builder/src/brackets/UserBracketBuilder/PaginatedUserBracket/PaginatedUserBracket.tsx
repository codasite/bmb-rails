import React, { } from 'react';
// import { Bracket } from '../../bracket/components/Bracket';
// import { Bracket } from '../../bracket/components/Bracket';
import { useAppSelector, useAppDispatch } from '../../shared/app/hooks'
import { nextPage, selectCurrentPage, selectNumPages } from '../../shared/features/bracketNavSlice';

import { MatchTree } from '../../shared/models/MatchTree';
import { PaginatedLandingPage, PaginatedRound, PaginatedBracketResult } from './components'


interface PaginatedBracketProps {
	matchTree?: MatchTree;
	canPick?: boolean;
}

export const PaginatedUserBracket = (props: PaginatedBracketProps) => {
	const {
		matchTree,
		canPick
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
		} else if (currentPage < numPages - 1) {
			return <PaginatedRound canPick={canPick} />
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