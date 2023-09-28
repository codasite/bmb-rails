import React, { useEffect } from 'react';
import { BracketMeta, DarkModeContext } from '../../shared/context';
import { MatchTree } from '../../shared/models/MatchTree';
import { PlayRes } from '../../shared/api/types/bracket';
import { PickableBracket } from '../../shared/components/Bracket';

interface PrintPlayPageProps {
	bracketMeta: BracketMeta
	setBracketMeta: (bracketMeta: BracketMeta) => void
	matchTree: MatchTree
	setMatchTree: (matchTree: MatchTree) => void
	bracketPlay: PlayRes
	position: string // top, center, bottom. Default is top
	inchHeight: number // height of the bracket in inches. Default is 16
	inchWidth: number // width of the bracket in inches. Default is 12
	scaleFactor: number // scale factor for the bracket. Default is 1
	darkMode: boolean // whether to use dark mode. Default is false

}
const PrintPlayPage = (props: PrintPlayPageProps) => {
	const {
		setBracketMeta,
		darkMode,
		matchTree,
		setMatchTree,
		bracketPlay: play,
		position,
		inchHeight,
		inchWidth,
	} = props

	useEffect(() => {
		const picks = play.picks
		const title = play.tournament?.title
		const date = 'Sept 2094'
		setBracketMeta({ title, date })
		const template = play.tournament.bracketTemplate
		if (picks && template) {
			const tree = MatchTree.fromPicks(template.numTeams, template.matches, picks)

			if (tree) {
				setMatchTree(tree)
			}
		}
	}, [play])

	let justify = 'flex-start'

	if (position === 'center') {
		justify = 'center'
	} else if (position === 'bottom') {
		justify = 'flex-end'
	}

	const heightPx = inchHeight * 96
	const widthPx = inchWidth * 96

	return (
		<DarkModeContext.Provider value={darkMode}>
			<div className={`wpbb-reset tw-bg-none tw-flex tw-flex-col tw-items-center tw-justify-${justify} tw-h-[${heightPx}px] tw-w-[${widthPx}px]${darkMode ? ' tw-dark' : ''}`}>
				<PickableBracket
					matchTree={matchTree}
				/>
			</div>
		</DarkModeContext.Provider>
	)

}