import React, { useEffect, useContext, useState, createContext } from 'react'
import * as Sentry from '@sentry/react'
import { ThemeSelector } from '../../shared/components'
import { MatchTree } from '../../shared/models/MatchTree'
import { BusterBracket, PickableBracket } from '../../shared/components/Bracket'
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
import { MatchRes, PlayReq, PlayRes } from '../../shared/api/types/bracket'
import { DarkModeContext } from '../../shared/context'
import {
  BusterMatchTreeContext,
  BusteeMatchTreeContext,
} from '../../shared/context'
import { ProfilePicture } from '../../shared/components/ProfilePicture'
import { BusterVsBustee } from './BusterVersusBustee'

interface BustPlayBuilderProps {
  matchTree: MatchTree
  setMatchTree: (matchTree: MatchTree) => void
  busteePlay: PlayRes
  redirectUrl: string
}

export const BustPlayBuilder = (props: BustPlayBuilderProps) => {
  const { matchTree, setMatchTree, busteePlay, redirectUrl } = props

  const [busterMatchTree, setBusterMatchTree] = useState<MatchTree>()
  const [busteeMatchTree, setBusteeMatchTree] = useState<MatchTree>()
  const [busteeThumbnail, setBusteeThumbnail] = useState<string>('')
  const [busteeDisplayName, setBusteeDisplayName] = useState<string>('')
  const [processing, setProcessing] = useState<boolean>(false)

  useEffect(() => {
    handleVersus()
    buildMatchTrees()
  }, [])

  const handleVersus = () => {
    const busteeName = busteePlay?.authorDisplayName
    const busteeThumbnail = busteePlay?.thumbnailUrl
    setBusteeDisplayName(busteeName)
    setBusteeThumbnail(busteeThumbnail)
  }

  const buildMatchTrees = () => {
    const bracket = busteePlay?.bracket
    const matches = bracket?.matches
    const numTeams = bracket?.numTeams
    const tree = MatchTree.fromMatchRes(numTeams, matches)
    setBusterMatchTree(tree)
    setBusteeMatchTree(matchTree.clone())
  }

  const handleSubmit = () => {
    const picks = busterMatchTree?.toMatchPicks()
    const bracketId = busteePlay?.bracket?.id
    const busteeId = busteePlay?.id

    if (!bracketId || !busteeId || !picks) {
      const msg =
        'Cannot create play. Missing one of bracketId, busteeId, or picks'
      console.error(msg)
      Sentry.captureException(msg)
      return
    }

    bracketApi.createPlay

    const playReq: PlayReq = {
      bracketId: bracketId,
      picks: picks,
      bustedId: busteeId,
    }

    setProcessing(true)
    bracketApi
      .createPlay(playReq)
      .then((res) => {
        window.location.href = redirectUrl
      })
      .catch((err) => {
        console.error(err)
        Sentry.captureException(err)
      })
      .finally(() => {
        setProcessing(false)
      })

    window.location.href = redirectUrl
  }

  const setBusterTree = (tree: MatchTree) => {
    setBusterMatchTree(tree.clone())
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
                setMatchTree: setBusterTree,
              }}
            >
              <BusterVsBustee
                busteeDisplayName={busteeDisplayName}
                busteeThumbnail={busteeThumbnail}
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
