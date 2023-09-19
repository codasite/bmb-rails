import React, { useEffect } from 'react';
import { ThemeSelector } from '../../shared/components';
import { MatchTree } from '../../shared/models/MatchTree';
import { PickableBracket } from '../../shared/components/Bracket';
import { ActionButton } from '../../shared/components/ActionButtons';
import { WithDarkMode, WithMatchTree, WithBracketMeta, WithProvider } from '../../shared/components/HigherOrder'
//@ts-ignore
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
//@ts-ignore
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { BracketMeta } from '../../shared/context';

interface ViewPlayPageProps {
	bracketMeta: BracketMeta
	setBracketMeta: (bracketMeta: BracketMeta) => void
	matchTree: MatchTree
	setMatchTree: (matchTree: MatchTree) => void
	bracketPlay: any
	apparelUrl: string
	darkMode: boolean
	setDarkMode: (darkMode: boolean) => void
}

const ViewPlayPage = (props: ViewPlayPageProps) => {

	const {
		bracketMeta,
		setBracketMeta,
		darkMode,
		setDarkMode,
		matchTree,
		setMatchTree,
		bracketPlay: play,
	} = props

	useEffect(() => {
		console.log('play', play)
		const picks = play?.picks
		const title = play?.tournament?.title
		const date = 'Sept 2094'
		setBracketMeta({ title, date })
		const template = play?.tournament?.bracketTemplate
		const matches = template?.matches
		const numTeams = template?.numTeams
		// if (picks && matches) {
		if (matches) {
			// const tree = MatchTree.fromPicks(numTeams, matches, picks)
			const tree = MatchTree.fromMatchRes(numTeams, matches)

			if (tree) {
				setMatchTree(tree)
			}
		}

	}, [play])
	return (
		<div className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${darkMode ? ' tw-dark' : ''}`} style={{ 'backgroundImage': `url(${darkMode ? darkBracketBg : lightBracketBg})` }}>
			<div className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto`}>
				{matchTree &&
					<>
						<div className='tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center'>
							<ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
						</div>
						<PickableBracket
							matchTree={matchTree}
						/>
						<div className='tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center'>
							<ActionButton
								variant='big-green'
								darkMode={darkMode}
							>Add to Apparel</ActionButton>
						</div>
					</>
				}
			</div>
		</div>
	)
}

const WrappedViewPlayPage = WithProvider(WithMatchTree(WithBracketMeta(WithDarkMode(ViewPlayPage))))
// export { WrappedViewPlayPage as ViewPlayPage }
export default WrappedViewPlayPage