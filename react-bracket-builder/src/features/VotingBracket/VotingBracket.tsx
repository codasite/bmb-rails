import { useContext } from 'react'
import { PickableBracket } from '../../brackets/shared/components/Bracket'
import { BracketProps } from '../../brackets/shared/components/types'
import { VotingTeamSlot } from './VotingTeamSlot'
import { DarkModeContext } from '../../brackets/shared/context/context'

export const VotingBracket = (props: BracketProps) => {
  const getLineStyle = (
    roundIndex: number,
    matchIndex: number,
    position: string
  ) => {
    const { darkMode } = useContext(DarkModeContext)
    return {
      className: `!tw-border-t-[1px] ${
        roundIndex > props.matchTree.liveRoundIndex
          ? darkMode
            ? '!tw-border-t-[#4c4662]'
            : '!tw-border-t-[#bfc0cc]'
          : darkMode
          ? '!tw-border-t-white'
          : '!tw-border-t-dd-blue'
      }`,
    }
  }
  return (
    <PickableBracket
      {...props}
      TeamSlotComponent={VotingTeamSlot}
      getLineStyle={getLineStyle}
    />
  )
}
