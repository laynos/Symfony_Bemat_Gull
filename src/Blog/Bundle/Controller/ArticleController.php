<?php

namespace Blog\Bundle\Controller;

use Blog\Bundle\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends Controller
{

	    public function indexAction()
    {
		$repository = $this
			->getDoctrine()
			->getManager()
			->getRepository('BlogBundle:Article')
		;


		$listArticles = $repository->findBy(
  array(), 
  array('date' => 'desc'),        
  100,                        
  0                            
);

    return $this->render('BlogBundle:Home:index.html.twig', array(
  'listArticles' => $listArticles
));
    }

	public function viewAction($id)
  {

	$article = $this->getDoctrine()
	->getManager()
	->find('BlogBundle:Article', $id)
	;

	 if (null === $article) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }


    return $this->render('BlogBundle:Article:view.html.twig', array(
      'article' => $article
    ));
  }

	public function menuAction($limit = 3)
  {
	  		$repository = $this
			->getDoctrine()
			->getManager()
			->getRepository('BlogBundle:Article')
			;



	$listArticles = $repository->findBy(
  array(), 
  array('date' => 'desc'),        
  $limit,                        
  0                           
);

    return $this->render('BlogBundle:Article:menu.html.twig', array(
      'listArticles' => $listArticles
    ));
  }
  	public function addAction(Request $request)
  {
	$em = $this->getDoctrine()->getManager();
	$article = new Article();
	$article->setDate(new \Datetime());
	
    $formBuilder = $this->get('form.factory')->createBuilder('form', $article);

    $formBuilder
      ->add('date',      'date')
      ->add('title',     'text')
      ->add('content',   'textarea')
      ->add('author',    'text')
      ->add('published', 'checkbox')
      ->add('save',      'submit')
    ;

    $form = $formBuilder->getForm();
	$formBuilder->add('published', 'checkbox', array('required' => false));
	$form->handleRequest($request);

	if ($form->isValid()) {

      $em->persist($article);
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

      return $this->redirect($this->generateUrl('blog_view', array('id' => $article->getId())));
    }

	return $this->render('BlogBundle:Article:add.html.twig', array(
      'form' => $form->createView(),
    ));
  }
  
   public function editAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    $article = $em->getRepository('BlogBundle:Article')->find($id);
	if (null === $article) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }	
   
    $formBuilder = $this->get('form.factory')->createBuilder('form', $article);
	$formBuilder
      ->add('date',      'date', array("data" => $article->getDate() ) )
      ->add('title',     'text', array("data" => $article->getTitle() ))
      ->add('content',   'textarea', array("data" => $article->getContent()))
      ->add('author',    'text', array("data" => $article->getAuthor()) )
      ->add('published', 'checkbox')
      ->add('save',      'submit')
    ;
	$form = $formBuilder->getForm();
	$form->handleRequest($request);

	
   if ($form->isValid()) {

      $em->persist($article);
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

      return $this->redirect($this->generateUrl('blog_view', array('id' => $article->getId())));
    }
	
	return $this->render('BlogBundle:Article:edit.html.twig', array(
      'form' => $form->createView(),'article' => $article
    ));
  }

  public function deleteAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    $article = $em->getRepository('BlogBundle:Article')->find($id);

    if ($article == null) {
      throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
    }

    if ($request->isMethod('POST')) {
		
      $request->getSession()->getFlashBag()->add('info', 'Annonce bien supprimée.');

      return $this->redirect($this->generateUrl('blog_homepage'));
    }
	$em->remove($article);
	$em->flush();
	
    return $this->render('BlogBundle:Article:delete.html.twig', array(
      'article' => $article
    ));
  }

}
