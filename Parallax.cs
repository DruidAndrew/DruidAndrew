using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Parallax : MonoBehaviour
{
    public float multiplier;
    public bool horizontalOnly;
    public bool calculateInfiniteHorisontalPosition;
    public bool calculateInfiniteVerticalPosition;
    public bool isInfinite;
    
    private GameObject camera;
    private Vector3 startPosition;
    private Vector3 startCameraPosition;
    private float length;
    
    
    // Start is called before the first frame update
    void Start()
    {
        camera = GameManager.Instance.camera.gameObject;
        startPosition = transform.position;
        startCameraPosition = camera.transform.position;
        if (isInfinite)
            length = GetComponent<SpriteRenderer>().bounds.size.x;
        
        CalculateStartPosition();
    }

    void CalculateStartPosition()
    {
        float distX = (camera.transform.position.x - transform.position.x) * multiplier;
        float distY = (camera.transform.position.y - transform.position.y) * multiplier;
        Vector3 tmp = new Vector3(startPosition.x, startPosition.y);

        if (calculateInfiniteHorisontalPosition)
            tmp.x = transform.position.x + distX;
        if (calculateInfiniteVerticalPosition)
            tmp.y = transform.position.y + distY;

        startPosition = tmp;
    }

    // Update is called once per frame
    void FixedUpdate()
    {
        Vector3 position = startPosition;

        if (horizontalOnly)
            position.x += multiplier * (camera.transform.position.x - startCameraPosition.x);
        else
            position += multiplier * (camera.transform.position - startCameraPosition);

        transform.position = position;

        if (isInfinite)
        {
            float tmp = (camera.transform.position.x * (1 - multiplier));
            if (tmp > startPosition.x + length)
                startPosition.x += length;
            else if (tmp < startPosition.x - length)
                startPosition.x -= length;
        }
    }
}
