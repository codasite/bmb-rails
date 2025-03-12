// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext } from 'react'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { PickableBracket } from '../../shared/components/Bracket'
import { PlayBuilderProps } from './types'
import { PlayBuilderButtons } from './PlayBuilderButtons'
import { DarkModeContext } from '../../shared/context/context'
import { BracketHeaderTag } from '../BracketHeaderTag'
import { VotingBracket } from '../../../features/VotingBracket/VotingBracket'
import { ThemeSelector } from '../../../ui/ThemeSelector'
import { BracketBackground } from '../../shared/components/BracketBackground'
export const PlayBuilder = (props: PlayBuilderProps) => {
  const { matchTree, setMatchTree } = props
  const bracketProps = {
    matchTree,
    setMatchTree,
  }
  return (
    <BracketBackground>
      {matchTree && (
        <div
          className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto tw-pb-80`}
        >
          <div className="tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center tw-gap-10">
            {matchTree.isVoting && (
              <BracketHeaderTag
                text={`Voting Round ${matchTree.liveRoundIndex + 1}`}
                color="green"
              />
            )}
            <ThemeSelector />
          </div>
          {matchTree.isVoting ? (
            <VotingBracket {...bracketProps} />
          ) : (
            <PickableBracket {...bracketProps} />
          )}
          <div className="tw-px-24 tw-mt-60 tw-flex tw-gap-15 tw-flex-col tw-items-stretch tw-self-stretch">
            <PlayBuilderButtons {...props} />
          </div>
        </div>
      )}
    </BracketBackground>
  )
}
