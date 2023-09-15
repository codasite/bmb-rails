import React, { } from 'react'
import './template-builder.scss'
import { MatchTree } from '../shared/models/MatchTree'
//@ts-ignore
import { ReactComponent as ArrowNarrowLeft } from '../shared/assets/arrow-narrow-left.svg'
//@ts-ignore
import iconBackground from '../shared/assets/bmb_icon_white_02.png'
import { DefaultBracket } from '../shared/components'
import { BracketMetaContext, DarkModeContext } from '../shared/context'
import { AddTeamsBracket } from '../shared/components/Bracket'

interface AddTeamsPageProps {
	bracketTitle: string
	matchTree?: MatchTree
	setMatchTree?: (matchTree: MatchTree) => void
	handleSaveTemplate: () => void
	handleSaveTournament: () => void
	handleBack: () => void
}

export const AddTeamsPage = (props: AddTeamsPageProps) => {
	const {
		bracketTitle,
		matchTree,
		setMatchTree,
		handleSaveTemplate,
		handleSaveTournament,
		handleBack
	} = props

	return (
		<div className='tw-flex tw-flex-col tw-gap-60 tw-pt-30 tw-pb-60 tw-bg-no-repeat tw-bg-top tw-bg-cover' style={{ 'background': `url(${iconBackground}), #000225` }}>
			<div className='tw-px-60'>
				<a href="#" className='tw-flex tw-gap-10 tw-color-white tw-p-16' onClick={handleBack}>
					<ArrowNarrowLeft />
					<span className='tw-font-500 tw-text-20 tw-text-white '>Create Template</span>
				</a>
			</div>
			<div className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-xl tw-m-auto tw-dark`}>
				{
					matchTree &&
					<DarkModeContext.Provider value={true}>
						<BracketMetaContext.Provider value={{ title: bracketTitle, date: '2021' }}>
							<AddTeamsBracket
								matchTree={matchTree}
								setMatchTree={setMatchTree}
							/>
						</BracketMetaContext.Provider>
					</DarkModeContext.Provider>
				}
			</div>
		</div>
		// <div className="bracket-container">
		// 	<div><button className='create-bracket' onClick={handleRedirect}><ArrowNarrowLeft />CREATE BRACKET</button></div>
		// 	<div className='bracket-title'>{bracketTitle}</div>
		// 	<div className='paired-bracket'>
		// 		<DarkModeContext.Provider value={true}>
		// 			<PairedBracket {...bracketProps} />
		// 		</DarkModeContext.Provider>
		// 	</div>
		// 	<div className={`randomize-team-container wpbb-bracket-actions`}>
		// 		<button className='randomize-teams no-highlight-button' onClick={handleShuffle} >
		// 			<ShuffleIcon />
		// 			<span className={'randomize-teams-text'}>scramble team order</span>
		// 		</button>
		// 	</div>
		// 	<div className='bracket-button'>
		// 		<button className='btn-save-bracket' onClick={handleSave}>
		// 			<SaveIcon />
		// 			<span className='save-bracket-text'>Save As Template</span>
		// 		</button>
		// 		<button className='btn-play-bracket' >
		// 			<PlayIcon />
		// 			<span className='play-bracket-text'>Create Tournament</span>
		// 		</button>
		// 	</div>
		// </div>
	)

}