import { useContext, useEffect, useState } from 'react'
import { bracketApi } from '../../shared/api/bracketApi'
import { Nullable } from '../../../utils/types'
import { MatchTree } from '../../shared/models/MatchTree'
import {
  BracketMeta,
  BracketMetaContext,
  MatchTreeContext,
} from '../../shared/context/context'
import { BracketRes, PlayReq, PlayRes } from '../../shared/api/types/bracket'
import { PaginatedPlayBuilder } from '../PaginatedPlayBuilder/PaginatedPlayBuilder'
import { PlayBuilder } from './PlayBuilder'
import {
  getBracketMeta,
  getBracketWidth,
} from '../../shared/components/Bracket/utils'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'
import { WithWindowDimensions } from '../../shared/components/HigherOrder/WithWindowDimensions'
import { WindowDimensionsContext } from '../../shared/context/WindowDimensionsContext'
import { PlayStorage } from '../../shared/storages/PlayStorage'
import SubmitPicksRegisterModal from './SubmitPicksRegisterModal'
import StripePaymentModal from './StripePaymentModal'
import { logger } from '../../../utils/Logger'
import mergePicksFromPlayAndResults from '../../../features/VotingBracket/mergePicksFromPlayAndResults'

const PlayBuilderPage = (props: {
  // for testing
  matchTree?: MatchTree
  bracketProductArchiveUrl: string
  myPlayHistoryUrl: string
  isUserLoggedIn: boolean
  bracket?: BracketRes
  play?: PlayRes
  userCanPlayBracketForFree?: boolean
  loginUrl: string
}) => {
  const {
    bracket,
    bracketProductArchiveUrl,
    myPlayHistoryUrl,
    isUserLoggedIn,
    userCanPlayBracketForFree,
    play,
  } = props

  let tree: MatchTree
  if (props.matchTree) {
    tree = props.matchTree
  } else if (bracket.isVoting) {
    tree = MatchTree.fromPicks(
      bracket,
      mergePicksFromPlayAndResults(
        bracket.results || [],
        play?.picks,
        bracket.liveRoundIndex
      )
    )
  } else if (play) {
    tree = MatchTree.fromPicks(bracket, play.picks)
  } else {
    tree = MatchTree.fromMatchRes(bracket)
  }
  const [bracketMeta, setBracketMeta] = useState<BracketMeta>(
    getBracketMeta(bracket)
  )
  const [matchTree, setMatchTree] = useState<MatchTree>(tree)
  const [processingAddToApparel, setProcessingAddToApparel] = useState(false)
  const [addToApparelError, setAddToApparelError] = useState(false)
  const [submitPicksError, setSubmitPicksError] = useState(false)
  const [processingSubmitPicks, setProcessingSubmitPicks] = useState(false)
  const [storedPlay, setStoredPlay] = useState<Nullable<PlayReq>>(null)
  const [showRegisterModal, setShowRegisterModal] = useState(!isUserLoggedIn)
  const [showPaymentModal, setShowPaymentModal] = useState(false)
  const [stripeClientSecret, setStripeClientSecret] = useState<string>('')
  const [stripePaymentAmount, setStripePaymentAmount] = useState<number>(null)
  const { width: windowWidth } = useContext(WindowDimensionsContext)

  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(bracket?.numTeams))

  const canPrint = bracket?.isPrintable
  const canSubmit = bracket?.isOpen && props.isUserLoggedIn
  const playStorage = new PlayStorage('loadStoredPicks', 'wpbb_play_data_')
  const paymentRequired = bracket?.isOpen && !userCanPlayBracketForFree // TODO: decide if we need to check if the bracket is open. Is this handled by canSubmit?
  const loginRedirectUrl =
    props.loginUrl +
    (props.bracket?.url ? `?redirect=${props.bracket.url}` : '')

  useEffect(() => {
    const stored = playStorage.loadPlay(bracket?.id)
    if (stored) {
      const tree = MatchTree.fromPicks(bracket, stored.picks)
      setMatchTreeAndSaveInStorage(tree)
    }
  }, [])

  const clearError = () => {
    setAddToApparelError(false)
    setSubmitPicksError(false)
  }

  const setMatchTreeAndSaveInStorage = (tree: MatchTree) => {
    setMatchTree(tree.clone())
    playStorage.storePlay(
      {
        bracketId: bracket?.id,
        picks: tree.toMatchPicks(),
        id: playStorage.loadPlay(bracket?.id)?.id,
      },
      bracket?.id
    )
  }

  const shouldNotCreateNewPlay = (playReq: PlayReq) => {
    return (
      storedPlay?.id &&
      JSON.stringify(storedPlay?.picks) === JSON.stringify(playReq.picks)
    )
  }

  const getPlayReq = () => {
    const picks = matchTree?.toMatchPicks()
    const bracketId = bracket?.id
    if (!picks || !bracketId) {
      const msg = 'Cannot create play. Missing picks'
      logger.error(msg)
      return
    }
    const playReq: PlayReq = {
      title: bracket?.title,
      bracketId: bracketId,
      picks: picks,
    }
    return playReq
  }

  const handleApparelClick = async () => {
    clearError()
    setProcessingAddToApparel(true)
    const playReq = getPlayReq()
    playReq.generateImages = true
    try {
      if (shouldNotCreateNewPlay(playReq)) {
        // regenerate play images anyway (in case they don't exist)
        await bracketApi.generatePlayImages(storedPlay.id)
      } else {
        const res = await bracketApi.createPlay(playReq)
        const playId = res.id
        const newReq = {
          ...playReq,
          id: playId,
        }
        playStorage.storePlay(newReq, bracket?.id)
      }
      window.location.assign(bracketProductArchiveUrl)
    } catch (err) {
      setProcessingAddToApparel(false)
      setAddToApparelError(true)
      logger.error(err)
    }
  }

  const handleSubmitPicksClick = async () => {
    clearError()
    setProcessingSubmitPicks(true)
    const playReq = getPlayReq()
    playReq.generateImages = false
    playReq.createStripePaymentIntent = paymentRequired

    if (shouldNotCreateNewPlay(playReq)) {
      if (!paymentRequired) {
        if (isUserLoggedIn) {
          window.location.assign(myPlayHistoryUrl)
        } else {
          setShowRegisterModal(true)
        }
        return
      }
      if (stripeClientSecret) {
        setShowPaymentModal(true)
        return
      }
      await bracketApi
        .createStripePaymentIntent({ playId: storedPlay.id })
        .then((res) => {
          setStripeClientSecret(res.clientSecret)
          setStripePaymentAmount(res.amount)
          setProcessingSubmitPicks(false)
          setShowPaymentModal(true)
        })
        .catch((err) => {
          setProcessingSubmitPicks(false)
          setSubmitPicksError(true)
          logger.error(err)
        })

      return
    }
    return bracketApi
      .createPlay(playReq)
      .then((res) => {
        const playId = res.id
        const newReq = {
          ...playReq,
          id: playId,
        }
        playStorage.storePlay(newReq, bracket?.id)
        setStoredPlay(newReq)
        if (paymentRequired) {
          setStripeClientSecret(res.stripePaymentIntentClientSecret)
          setStripePaymentAmount(res.stripePaymentAmount)
          setShowPaymentModal(true)
          setProcessingSubmitPicks(false)
        } else if (isUserLoggedIn) {
          window.location.assign(myPlayHistoryUrl)
        } else {
          setShowRegisterModal(true)
        }
      })
      .catch((err) => {
        setProcessingSubmitPicks(false)
        logger.error(err)
      })
  }

  const playBuilderProps = {
    matchTree,
    setMatchTree:
      canPrint || canSubmit ? setMatchTreeAndSaveInStorage : undefined,
    handleApparelClick: canPrint ? handleApparelClick : undefined,
    handleSubmitPicksClick: canSubmit ? handleSubmitPicksClick : undefined,
    canPlay: canPrint || canSubmit,
    processingAddToApparel,
    processingSubmitPicks,
    addToApparelError,
    submitPicksError,
    bracketMeta,
    setBracketMeta,
  }

  return (
    <MatchTreeContext.Provider
      value={{
        matchTree: matchTree,
        setMatchTree: setMatchTreeAndSaveInStorage,
      }}
    >
      <BracketMetaContext.Provider value={bracketMeta}>
        <SubmitPicksRegisterModal
          show={showRegisterModal}
          setShow={setShowRegisterModal}
          loginUrl={loginRedirectUrl}
        />
        <StripePaymentModal
          title={'Submit Your Picks'}
          show={showPaymentModal}
          setShow={setShowPaymentModal}
          clientSecret={stripeClientSecret}
          paymentAmount={stripePaymentAmount}
          myPlayHistoryUrl={myPlayHistoryUrl}
        />
        {showPaginated ? (
          <PaginatedPlayBuilder {...playBuilderProps} />
        ) : (
          <PlayBuilder {...playBuilderProps} />
        )}
      </BracketMetaContext.Provider>
    </MatchTreeContext.Provider>
  )
}

const WrappedPlayBuilderPage = WithWindowDimensions(PlayBuilderPage)

export default WrappedPlayBuilderPage
