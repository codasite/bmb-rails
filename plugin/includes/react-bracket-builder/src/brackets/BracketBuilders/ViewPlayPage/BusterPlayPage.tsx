import React, { useEffect, useState } from 'react'
import { ThemeSelector } from '../../shared/components'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'
//@ts-ignore
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
//@ts-ignore
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { BracketMeta } from '../../shared/context'
import { getBracketMeta } from '../../shared/utils'
import { ViewPlayPageProps } from './types'
import { BusterVsBustee } from '../BustPlayPage/BusterVersusBustee'
import {
  BusterMatchTreeContext,
  BusteeMatchTreeContext,
} from '../../shared/context'

export const BusterPlayPage = (props: ViewPlayPageProps) => {
  const {
    bracketMeta,
    setBracketMeta,
    darkMode,
    setDarkMode,
    matchTree,
    setMatchTree,
    bracketPlay: play,
    apparelUrl,
  } = props

  const [busterMatchTree, setBusterMatchTree] = useState<MatchTree>()
  const [busteeMatchTree, setBusteeMatchTree] = useState<MatchTree>()
  const [busteeDisplayName, setBusteeDisplayName] = useState<string>()

  useEffect(() => {
    handleBracketMeta()
    buildMatchTrees()
  }, [])

  const handleBracketMeta = () => {
    const bracket = play.bracket
    const busteeName = play.bustedPlay?.authorDisplayName
    const busterName = play.authorDisplayName
    const title = `${busteeName} vs ${busterName}`
    const { date } = getBracketMeta(bracket)
    setBracketMeta({ title, date })
    setBusteeDisplayName(busteeName)
  }

  const buildMatchTrees = () => {
    const bracket = play.bracket
    const matches = bracket?.matches
    const busterPicks = play.picks
    const busteePicks = play.bustedPlay?.picks
    const numTeams = bracket?.numTeams
    const busterTree = MatchTree.fromPicks(numTeams, matches, busterPicks)
    const busteeTree = MatchTree.fromPicks(numTeams, matches, busteePicks)
    setBusterMatchTree(busterTree)
    setBusteeMatchTree(busteeTree)
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
        {matchTree && busterMatchTree && (
          <BusteeMatchTreeContext.Provider
            value={{
              matchTree: busteeMatchTree,
            }}
          >
            <BusterMatchTreeContext.Provider
              value={{
                matchTree: busterMatchTree,
              }}
            >
              <BusterVsBustee
                busteeDisplayName={busteeDisplayName}
                busteeThumbnail={thumbnailUrl}
              />
              <BusterBracket
                matchTree={matchTree}
                setMatchTree={setMatchTree}
              />
              <div className="tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center">
                {busterMatchTree.allPicked() ? (
                  <ActionButton
                    variant="big-red"
                    darkMode={true}
                    onClick={handleSubmit}
                  >
                    Submit
                  </ActionButton>
                ) : (
                  <ActionButton
                    variant="big-red"
                    darkMode={true}
                    disabled={true}
                    onClick={() => {}}
                  >
                    Submit
                  </ActionButton>
                )}
              </div>
            </BusterMatchTreeContext.Provider>
          </BusteeMatchTreeContext.Provider>
        )}
      </div>
    </div>
  )
}
