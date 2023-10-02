import React, { useEffect } from 'react';
import { BracketMeta, DarkModeContext } from '../../shared/context';
import { MatchTree } from '../../shared/models/MatchTree';
import { MatchPicks, MatchRes, PlayRes } from '../../shared/api/types/bracket';
import { PickableBracket } from '../../shared/components/Bracket';
import { WithBracketMeta, WithMatchTree, WithProvider, WithDarkMode } from '../../shared/components/HigherOrder';
import { camelCaseKeys } from '../../shared/api/bracketApi';

interface PrintBracketPageProps {
	bracketMeta: BracketMeta
	setBracketMeta: (bracketMeta: BracketMeta) => void
	matchTree: MatchTree
	setMatchTree: (matchTree: MatchTree) => void
	darkMode: boolean
	setDarkMode: (darkMode: boolean) => void
}

interface PrintParams {
	theme?: string;
	position?: string;
	inchHeight?: number;
	inchWidth?: number;
	title?: string;
	date?: string;
	picks?: MatchPicks[];
	matches?: MatchRes[];
	numTeams?: number;
}

const PrintPlayPage = (props: PrintBracketPageProps) => {
	const {
		bracketMeta,
		setBracketMeta,
		matchTree,
		setMatchTree,
		darkMode,
		setDarkMode,
	} = props

	const [position, setPosition] = React.useState('top')
	const [inchHeight, setInchHeight] = React.useState(16)
	const [inchWidth, setInchWidth] = React.useState(11)

	useEffect(() => {
		console.log('PrintPlayPage useEffect')

		const params = getParams()
		const errors = validateParams(params)

		if (errors.length > 0) {
			throw new Error(errors.join(', '))
		}

		const { theme, position, inchHeight, inchWidth, title, date, picks, matches, numTeams } = params

		setDarkMode(theme === 'dark')
		setPosition(position)
		setInchHeight(inchHeight)
		setInchWidth(inchWidth)
		setBracketMeta({ title, date })

		const tree = MatchTree.fromPicks(numTeams, matches, picks)
		console.log('tree', tree)

		if (tree) {
			setMatchTree(tree)
		}

	}, [])

	const getParams = (): PrintParams => {
		const urlParams = new URLSearchParams(window.location.search)
		const theme = urlParams.get('theme') || 'light'
		const position = urlParams.get('position') || 'top'
		const inchHeight = Number(urlParams.get('inch_height')) || 16
		const inchWidth = Number(urlParams.get('inch_width')) || 11
		const title = urlParams.get('title') || 'Winner'
		const date = urlParams.get('date') || ''
		const picks = camelCaseKeys(JSON.parse(decodeURIComponent(urlParams.get('picks'))))
		const matches = camelCaseKeys(JSON.parse(decodeURIComponent(urlParams.get('matches'))))
		const numTeams = Number(urlParams.get('num_teams'))

		return {
			theme,
			position,
			inchHeight,
			inchWidth,
			title,
			date,
			picks,
			matches,
			numTeams,
		}
	}

	const validateParams = (params: PrintParams) => {
		const { theme, position, inchHeight, inchWidth, title, date, picks, matches, numTeams } = params

		const errors = []

		if (!theme || !['light', 'dark'].includes(theme)) {
			errors.push('theme must be light or dark')
		}

		if (!position || !['top', 'center', 'bottom'].includes(position)) {
			errors.push('position must be top, center, or bottom')
		}

		if (!inchHeight || inchHeight < 1 || inchHeight > 100) {
			errors.push('inchHeight must be between 1 and 100')
		}

		if (!inchWidth || inchWidth < 1 || inchWidth > 100) {
			errors.push('inchWidth must be between 1 and 100')
		}

		if (!title || title.length > 100) {
			errors.push('title is required and must be less than 100 characters')
		}

		if (date && date.length > 100) {
			errors.push('date must be less than 100 characters')
		}

		if (!picks || picks.length < 1) {
			errors.push('picks is required')
		}

		if (!matches || matches.length < 1) {
			errors.push('matches is required')
		}

		if (!numTeams || numTeams < 1) {
			errors.push('numTeams is required')
		}

		return errors
	}

	let justify = 'flex-start'

	if (position === 'center') {
		justify = 'center'
	} else if (position === 'bottom') {
		justify = 'flex-end'
	}

	const heightPx = inchHeight * 96
	const widthPx = inchWidth * 96
	const { title: bracketTitle, date: bracketDate } = bracketMeta

	return (
		<div className={`wpbb-reset tw-py-60 tw-mx-auto tw-bg-${darkMode ? 'black' : 'white'} tw-flex tw-flex-col tw-items-center tw-justify-${justify} tw-h-[${heightPx}px] tw-w-[${widthPx}px]${darkMode ? ' tw-dark' : ''}`}>
			{matchTree && bracketTitle &&
				<PickableBracket
					matchTree={matchTree}
					darkMode={darkMode}
					title={bracketTitle}
					date={bracketDate}
				/>
			}
		</div>
	)

}

const WrappedPrintPlayPage = WithProvider(WithDarkMode(WithBracketMeta(WithMatchTree(PrintPlayPage))))

export default WrappedPrintPlayPage