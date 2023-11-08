import { useEffect, useState } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { ActionButton } from '../../shared/components/ActionButtons'
import redBracketBg from '../../shared/assets/bracket-bg-red.png'
import { getBracketMeta } from '../../shared/components/Bracket/utils'
import { ViewPlayPageProps } from './types'
import { BusterVsBustee } from '../BustPlayPage/BusterVersusBustee'
import { BusterBracket } from '../../shared/components/Bracket'
import {
  BusterMatchTreeContext,
  BusteeMatchTreeContext,
} from '../../shared/context/context'

export const BusterPlayPage = (props: ViewPlayPageProps) => {
  const {
    setBracketMeta,
    matchTree,
    setMatchTree,
    bracketPlay: play,
    myPlayHistoryUrl,
  } = props

  const [busterTree, setBusterTree] = useState<MatchTree>()
  const [busteeTree, setBusteeTree] = useState<MatchTree>()
  const [busteeDisplayName, setBusteeDisplayName] = useState<string>()
  const [busteeThumbnail, setBusteeThumbnail] = useState<string>()

  useEffect(() => {
    handleBracketMeta()
    buildMatchTrees()
  }, [])

  const handleBracketMeta = () => {
    const bracket = play.bracket
    const busteeName = play.bustedPlay?.authorDisplayName
    const busteeThumbnail = play.bustedPlay?.thumbnailUrl
    const busterName = play.authorDisplayName
    const bracketTitle = bracket?.title || 'Bracket'
    const title = `${busterName}'s Bust of ${busterName}'s ${bracketTitle} Picks`
    const { date } = getBracketMeta(bracket)
    setBracketMeta({ title, date })
    setBusteeDisplayName(busteeName)
    setBusteeThumbnail(busteeThumbnail)
  }

  const buildMatchTrees = () => {
    const bracket = play.bracket
    const matches = bracket?.matches
    const busterPicks = play.picks
    const busteePicks = play.bustedPlay?.picks
    const numTeams = bracket?.numTeams
    const busterTree = MatchTree.fromPicks(numTeams, matches, busterPicks)
    const busteeTree = MatchTree.fromPicks(numTeams, matches, busteePicks)
    setBusterTree(busterTree)
    setBusteeTree(busteeTree)
    setMatchTree(busterTree)
  }

  const handleBustAgain = async () => {
    window.location.href = myPlayHistoryUrl
  }

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover tw-dark`}
      style={{
        backgroundImage: `url(${redBracketBg})`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto`}
      >
        {matchTree && busteeTree && busterTree && (
          <BusteeMatchTreeContext.Provider
            value={{
              matchTree: busteeTree,
            }}
          >
            <BusterMatchTreeContext.Provider
              value={{
                matchTree: busterTree,
              }}
            >
              <BusterVsBustee
                busteeDisplayName={busteeDisplayName}
                busteeThumbnail={busteeThumbnail}
              />
              <BusterBracket matchTree={matchTree} />
              <div className="tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center">
                <ActionButton
                  variant="big-red"
                  darkMode={true}
                  onClick={handleBustAgain}
                >
                  Bust Again
                </ActionButton>
              </div>
            </BusterMatchTreeContext.Provider>
          </BusteeMatchTreeContext.Provider>
        )}
      </div>
    </div>
  )
}
