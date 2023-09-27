import React, { useState, useEffect } from 'react';
import { PlayBuilderProps } from '../types';
import { PaginatedPickableBracket } from '../../../shared/components/Bracket';
import { LandingPage } from './LandingPage';
import { PickableBracketPage } from './PickableBracketPage';


export const PaginatedPlayBuilder = (props: PlayBuilderProps) => {
	const {
		matchTree,
		setMatchTree,
	} = props

	const [page, setPage] = useState('landing')

	useEffect(() => {
		if (!matchTree) {
			return
		}
		if (matchTree.anyPicked()) {
			setPage('bracket')
		} else if (matchTree.allPicked()) {
			setPage('final')
		}
	}, [])

	const onStart = () => {
		setPage('bracket')
	}

	const onFinished = () => {
		setPage('final')
	}

	let element: JSX.Element | null = null

	switch (page) {
		case 'landing':
			element = <LandingPage
				matchTree={matchTree}
				onStart={onStart}
			/>
			break
		case 'bracket':
			element = <PickableBracketPage
				matchTree={matchTree}
				setMatchTree={setMatchTree}
				onFinished={onFinished}
			/>
			break
		case 'final':
			element = <div>Final</div>
			break
	}

	return element
}