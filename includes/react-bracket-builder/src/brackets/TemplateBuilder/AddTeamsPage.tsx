import React, { } from 'react'
import './template-builder.scss'
import { PairedBracket } from '../UserBracketBuilder/UserBracket/components/PairedBracket'
//@ts-ignore
import { ReactComponent as ArrowNarrowLeft } from '../shared/assets/arrow-narrow-left.svg'
//@ts-ignore
import { ReactComponent as SaveIcon } from '../shared/assets/save-icon.svg'
//@ts-ignore
import { ReactComponent as PlayIcon } from '../shared/assets/play-icon.svg'
//@ts-ignore
import { ReactComponent as ShuffleIcon } from '../shared/assets/shuffle-icon.svg'
import { DarkModeContext } from '../shared/context'


export const AddTeamsPage = (props) => {

	return (
		<div className="bracket-container">
			<div><button className='create-bracket' onClick={handleRedirect}><ArrowNarrowLeft />CREATE BRACKET</button></div>
			<div className='bracket-title'>{bracketTitle}</div>
			<div className='paired-bracket'>
				<DarkModeContext.Provider value={true}>
					<PairedBracket {...bracketProps} />
				</DarkModeContext.Provider>
			</div>
			<div className={`randomize-team-container wpbb-bracket-actions`}>
				<button className='randomize-teams no-highlight-button' onClick={handleShuffle} >
					<ShuffleIcon />
					<span className={'randomize-teams-text'}>scramble team order</span>
				</button>
			</div>
			<div className='bracket-button'>
				<button className='btn-save-bracket' onClick={handleSave}>
					<SaveIcon />
					<span className='save-bracket-text'>Save As Template</span>
				</button>
				<button className='btn-play-bracket' >
					<PlayIcon />
					<span className='play-bracket-text'>Create Tournament</span>
				</button>
			</div>
		</div>
	)

}