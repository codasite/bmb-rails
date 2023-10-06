import React, { useEffect, useContext, useState } from 'react'
import { ThemeSelector } from '../../shared/components'
import { MatchTree } from '../../shared/models/MatchTree'
import {
  BustableBracket,
  PickableBracket,
} from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import {
  WithDarkMode,
  WithMatchTree,
  WithBracketMeta,
  WithProvider,
} from '../../shared/components/HigherOrder'
//@ts-ignore
import redBracketBg from '../../shared/assets/bracket-bg-red.png'
//@ts-ignore
import { bracketApi } from '../../shared/api/bracketApi'
import { MatchRes, PlayRes } from '../../shared/api/types/bracket'
import { DarkModeContext, MatchTreeContext } from '../../shared/context'

interface BustPlayBuilderProps {
  matchTree: MatchTree
  setMatchTree: (matchTree: MatchTree) => void
  busteePlay: PlayRes
  redirectUrl: string
}

export const BustPlayBuilder = (props: BustPlayBuilderProps) => {
  const { matchTree: busteeMatchTree, busteePlay, redirectUrl } = props

  const [busterMatchTree, setBusterMatchTree] = useState<MatchTree>()

  useEffect(() => {
    const template = busteePlay?.tournament?.bracketTemplate
    const matches = template?.matches
    const numTeams = template?.numTeams
    const busterMatchTree = MatchTree.fromMatchRes(numTeams, matches)
    setBusterMatchTree(busterMatchTree)
  }, [])

  const handleSubmit = () => {
    // window.location.href = props.apparelUrl
    console.log('handleSubmit')
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
        {busteeMatchTree && busterMatchTree && (
          <MatchTreeContext.Provider
            value={{
              matchTree: busterMatchTree,
              setMatchTree: setBusterMatchTree,
            }}
          >
            <div className="tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center"></div>
            <BustableBracket matchTree={busteeMatchTree} />
            <div className="tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ActionButton
                variant="big-green"
                darkMode={true}
                onClick={handleSubmit}
              >
                Submit
              </ActionButton>
            </div>
          </MatchTreeContext.Provider>
        )}
      </div>
    </div>
  )
}
